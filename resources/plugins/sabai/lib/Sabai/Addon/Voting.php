<?php
class Sabai_Addon_Voting extends Sabai_Addon
    implements Sabai_Addon_System_IMainRouter,
               Sabai_Addon_System_IAdminRouter,
               Sabai_Addon_Field_ITypes,
               Sabai_Addon_Content_IPermissions,
               Sabai_Addon_Form_IFields
{
    const VERSION = '1.2.29', PACKAGE = 'sabai';
    
    const FLAG_VALUE_SPAM = 5, FLAG_VALUE_OFFENSIVE = 6, FLAG_VALUE_OFFTOPIC = 2, FLAG_VALUE_OTHER = 0;
                
    public function isUninstallable($currentVersion)
    {
        return false;
    }
    
    /* Start implementation of Sabai_Addon_System_IMainRouter */
    
    public function systemGetMainRoutes()
    {
        $routes = array();
        foreach ($this->_application->getModel('FieldConfig', 'Entity')->type_in($this->fieldGetTypeNames())->fetch()->with('Fields', 'Bundle') as $field_config) {                
            foreach ($field_config->Fields as $field) {
                if (!$field->Bundle) continue;

                $base_path = empty($field->Bundle->info['permalink_path'])
                    ? $field->Bundle->getPath() . '/:entity_id'
                    : $field->Bundle->info['permalink_path'] . '/:slug';
                if (!isset($routes[$base_path . '/vote'])) {
                    $routes[$base_path . '/vote'] = array();
                }
                $field_settings = $field->getFieldSettings();
                $tag = $field_settings['tag'];
                $routes[$base_path . '/vote/' . $tag] = array(
                    'controller' => 'VoteEntity',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'data' => array(
                        'tag' => $tag,
                        'check_perms' => !empty($field_config->settings['require_vote_permissions']),
                        'check_own' => !empty($field_config->settings['vote_own_permission_label']),
                        'allow_anonymous' => !empty($field_config->settings['allow_anonymous']),
                    ),
                    'callback_path' => 'vote_entity',
                    'access_callback' => true,
                );
                $routes[$base_path . '/vote/' . $tag . '/form'] = array(
                    'controller' => 'VoteEntityForm',
                    'callback_path' => 'vote_entity_form',
                    'title_callback' => true,
                );
                
                switch ($tag) {
                    case 'flag':
                        $routes[$base_path . '/voting/flags/ignore'] = array(
                            'controller' => 'IgnoreFlags',
                            'callback_path' => 'ignore_flags',
                            'access_callback' => true,
                            'title_callback' => true,
                        );
                        break;
                }
            }
        }

        return $routes;
    }

    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'vote_entity':                
                if ($this->_application->getUser()->isAnonymous()) {
                    $context->setUnauthorizedError($this->_application->Entity_Url($context->entity));
                    return false;
                }
                $context->voting_tag = $route['data']['tag'];
                if ($route['data']['check_perms']) {
                    // Check permission
                    if (!$this->_application->HasPermission($context->entity->getBundleName() . '_voting_' . $context->voting_tag)) {
                        $context->setError(__('You do not have the permission to perform this action.', 'sabai'));
                        return false;
                    }
                    if ($route['data']['check_own']) {
                        // Require additional permission to vote for own post
                        if ($context->entity->getAuthorId() === $this->_application->getUser()->id
                            && !$this->_application->HasPermission($context->entity->getBundleName() . '_voting_own_' . $context->voting_tag)
                        ) {
                            $context->setError(__('You do not have the permission to perform this action.', 'sabai'));
                            return false;
                        }
                    }
                }
                return true;
            case 'ignore_flags':
                // Require content moderation permission
                return $this->_application->HasPermission($context->entity->getBundleName() . '_manage');
        }
    }

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'vote_entity_form':
                return sprintf(__('Vote for "%s"', 'sabai'), $context->entity->getTitle());
            case 'ignore_flags':
                return __('Ignore Flags', 'sabai');
        }
    }

    /* End implementation of Sabai_Addon_System_IMainRouter */
    
    /* Start implementation of Sabai_Addon_System_IAdminRouter */
    
    public function systemGetAdminRoutes()
    {
        $routes = array();
        foreach ($this->_application->getModel('FieldConfig', 'Entity')->type_in($this->fieldGetTypeNames())->fetch()->with('Fields', 'Bundle') as $field_config) {                
            foreach ($field_config->Fields as $field) {
                if (!$field->Bundle) continue;

                $tag = substr($field->getFieldName(), strlen('voting_'));
                $routes[$field->Bundle->getPath() . '/:entity_id/voting_' . $tag] = array(
                    'controller' => ucfirst($tag),
                    'callback_path' => 'votes',
                    'access_callback' => true,
                    'title' => $field->getFieldTitle(),
                    'title_callback' => true,
                    'ajax' => 1,
                    'type' => Sabai::ROUTE_TAB,
                    'data' => array(
                        'tag' => $tag,
                    ),
                    'weight' => 5,
                );
            }
        }

        return $routes;
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'votes':        
                $context->voting_tag = $route['data']['tag'];
                return true;
        }
    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'votes':
                if ($titleType !== Sabai::ROUTE_TITLE_TAB) {
                    return $title;
                }
                if (in_array($context->voting_tag, array('rating', 'default'))) {
                    $count = @$context->entity->voting_rating['']['count'];
                } else {
                    $count = $context->entity->getSingleFieldValue('voting_' . $context->voting_tag, 'count');
                }
                return empty($count) ? $title : sprintf(__('%s (%d)', 'sabai'), $title, $count);
        }
    }

    /* End implementation of Sabai_Addon_System_IAdminRouter */
    
    /* Start implementation of Sabai_Addon_Field_ITypes */

    public function fieldGetTypeNames()
    {
        return array('voting_default', 'voting_updown', 'voting_rating', 'voting_favorite', 'voting_flag', 'voting_helpful');
    }

    public function fieldGetType($name)
    {
        return new Sabai_Addon_Voting_FieldType($this, $name);
    }

    /* End implementation of Sabai_Addon_Field_ITypes */
    
    /* Start implementation of Sabai_Addon_Content_IPermissions */

    public function contentGetPermissions(Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        $ret = array();
        foreach ($this->_application->Voting_TagSettings() as $tag => $settings) {
            if (empty($bundle->info['voting_' . $tag])
                || !isset($settings['require_vote_permissions'])
                || $settings['require_vote_permissions'] === false
            ) {
                continue;
            }
            $ret['voting_' . $tag] = $this->_application->Translate($settings['vote_permission_label']);
            if (!empty($settings['vote_own_permission_label'])) {
                $ret['voting_own_' . $tag] = $this->_application->Translate($settings['vote_own_permission_label']);
            }
            if (!empty($settings['require_vote_down_permission'])) {
                $ret['voting_down_' . $tag] = $this->_application->Translate($settings['vote_down_permission_label']);
            }
        }
        return $ret;
    }
    
    public function contentGetDefaultPermissions(Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        $ret = array();
        foreach ($this->_application->Voting_TagSettings() as $tag => $settings) {
            if (empty($bundle->info['voting_' . $tag])
                || !isset($settings['require_vote_permissions'])
                || $settings['require_vote_permissions'] === false
            ) {
                continue;
            }
            $ret[] = 'voting_' . $tag;
            $ret[] = 'voting_down_' . $tag;
        }
        return $ret;
    }

    /* End implementation of Sabai_Addon_Content_IPermissions */
    
    public function onEntityCreateBundlesSuccess($entityType, $bundles)
    {
        if (!in_array($entityType, array('content'))) return;

        $reload = false;
        foreach ($bundles as $bundle) {
            if (!empty($bundle->info['voting_tags'])) {
                foreach ($bundle->info['voting_tags'] as $tag_name => $settings) {
                    if ($this->_createVotingEntityField($bundle, $tag_name, $settings)) {
                        $reload = true;
                    }
                }
            }
            if (!empty($bundle->info['voting_updown'])) {
                if ($this->_createVotingUpdownEntityField($bundle)) {
                    $reload = true;
                }
            }
            if (!empty($bundle->info['voting_helpful'])) {
                if ($this->_createVotingHelpfulEntityField($bundle)) {
                    $reload = true;
                }
            }
            if (!empty($bundle->info['voting_rating'])) {
                if ($this->_createVotingRatingEntityField($bundle)) {
                    $reload = true;
                }
            }
            if (!empty($bundle->info['voting_favorite'])) {
                if ($this->_createVotingFavoriteEntityField($bundle)) {
                    $reload = true;
                }
            }
            if (!empty($bundle->info['voting_flag'])) {
                if ($this->_createVotingFlagEntityField($bundle)) {
                    $reload = true;
                }
            }
        }
        if ($reload) {
            // Reload system routing tables to reflect changes
            $this->_application->getAddon('System')->reloadRoutes($this)->reloadRoutes($this, true);
        }
    }
    
    private function _createVotingUpdownEntityField(Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return $this->_application->getAddon('Entity')->createEntityField(
            $bundle,
            'voting_updown',
            array(
                'type' => 'voting_updown',
                'title' => isset($bundle->info['voting_updown']['title']) ? $bundle->info['voting_updown']['title'] : null,
                'settings' => array(
                    'tag' => 'updown',
                    'min' => -1,
                    'max' => 1,
                    'step' => 1,
                    'allow_empty' => false,
                    'require_vote_permissions' => true,
                    'require_vote_down_permission' => true,
                    'vote_permission_label' => _n_noop('Vote up %s', 'sabai'),
                    'vote_own_permission_label' => _n_noop('Vote up own %s', 'sabai'),
                    'vote_down_permission_label' => _n_noop('Vote down %s', 'sabai'),
                ),
                'weight' => 99,
                'max_num_items' => 1, // Only 1 entry per entity should be created
            ),
            Sabai_Addon_Entity::FIELD_REALM_ALL
        );
    }
    
    private function _createVotingHelpfulEntityField(Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return $this->_application->getAddon('Entity')->createEntityField(
            $bundle,
            'voting_helpful',
            array(
                'type' => 'voting_helpful',
                'title' => isset($bundle->info['voting_helpful']['title']) ? $bundle->info['voting_helpful']['title'] : null,
                'settings' => array(
                    'tag' => 'helpful',
                    'min' => 0,
                    'max' => 1,
                    'step' => 1,
                    'allow_empty' => true,
                    'require_vote_permissions' => false,
                    'require_vote_down_permission' => false,
                ),
                'weight' => 99,
                'max_num_items' => 1, // Only 1 entry per entity should be created
            ),
            Sabai_Addon_Entity::FIELD_REALM_ALL
        );
    }
    
    private function _createVotingRatingEntityField(Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return $this->_application->getAddon('Entity')->createEntityField(
            $bundle,
            'voting_rating',
            array(
                'type' => 'voting_rating',
                'title' => isset($bundle->info['voting_rating']['title']) ? $bundle->info['voting_rating']['title'] : null,
                'settings' => array(
                    'tag' => 'rating',
                    'min' => 0,
                    'max' => 5,
                    'step' => 0.5,
                    'allow_empty' => true,
                    'allow_multiple' => true,
                    'require_vote_permissions' => true,
                    'require_vote_down_permission' => false,
                    'vote_permission_label' => _n_noop('Rate %s', 'sabai'),
                    'vote_own_permission_label' => _n_noop('Rate own %s', 'sabai'),
                ),
                'weight' => 99,
                'max_num_items' => 1, // Only 1 entry per entity should be created
            ),
            Sabai_Addon_Entity::FIELD_REALM_ALL
        );
    }
    
    private function _createVotingFavoriteEntityField(Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return $this->_application->getAddon('Entity')->createEntityField(
            $bundle,
            'voting_favorite',
            array(
                'type' => 'voting_favorite',
                'title' => isset($bundle->info['voting_favorite']['title']) ? $bundle->info['voting_favorite']['title'] : null,
                'settings' => array(
                    'tag' => 'favorite',
                    'min' => 1,
                    'max' => 1,
                    'step' => 1,
                    'allow_empty' => false,
                    'require_vote_permissions' => false,
                    'require_vote_down_permission' => false,
                ),
                'weight' => 99,
                'max_num_items' => 1, // Only 1 entry per entity should be created
            ),
            Sabai_Addon_Entity::FIELD_REALM_ALL
        );
    }
    
    private function _createVotingFlagEntityField(Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return $this->_application->getAddon('Entity')->createEntityField(
            $bundle,
            'voting_flag',
            array(
                'type' => 'voting_flag',
                'title' => isset($bundle->info['voting_flag']['title']) ? $bundle->info['voting_flag']['title'] : null,
                'settings' => array(
                    'tag' => 'flag',
                    'min' => self::FLAG_VALUE_OTHER,
                    'max' => self::FLAG_VALUE_OFFENSIVE,
                    'step' => 1,
                    'allow_empty' => true,
                    'require_vote_permissions' => true,
                    'require_vote_down_permission' => false,
                    'vote_permission_label' => _n_noop('Flag %s', 'sabai'),
                    'form_title' => __('Reason for flagging', 'sabai'),
                    'form_options' => $this->_application->Voting_FlagOptions(),
                    'form_other_option' => self::FLAG_VALUE_OTHER,
                    'form_default_value' => self::FLAG_VALUE_SPAM,
                    'form_redo_msg' => __('You have already flagged this %s. Press the button to redo flagging.', 'sabai'),
                    'form_redo_btn' => __('Redo Flagging', 'sabai'),
                    'form_submit_btn' => __('Flag %s', 'sabai'),
                    'form_success_msg' => __('Thanks, we will take a look at it.', 'sabai'),
                ),
                'weight' => 99,
                'max_num_items' => 1, // Only 1 entry per entity should be created
            ),
            Sabai_Addon_Entity::FIELD_REALM_ALL,
            true
        );
    }
    
    private function _createVotingEntityField(Sabai_Addon_Entity_Model_Bundle $bundle, $tagName, array $settings)
    {
        $min = isset($settings['min']) ? (int)$settings['min'] : 0;
        $max = isset($settings['max']) ? (int)$settings['max'] : 1;
        if ($max < $min) {
            return; // invalid min/max value settings
        }
        return $this->_application->getAddon('Entity')->createEntityField(
            $bundle,
            $bundle->name . '_' . $tagName,
            array(
                'type' => 'voting_default',
                'settings' => array(
                    'tag' => $tagName,
                    'min' => $min,
                    'max' => $max,
                    'step' => isset($settings['step']) ? (int)$settings['step'] : 1,
                    'allow_empty' => !empty($settings['allow_empty']),
                    'require_vote_permissions' => !empty($settings['require_vote_permissions']),
                    'require_vote_down_permission' => $min < 0 && !empty($settings['require_vote_down_permission']),
                    'vote_permission_label' => isset($settings['vote_permission_label']) ? (string)$settings['vote_permission_label'] : null,
                    'vote_own_permission_label' => isset($settings['vote_own_permission_label']) ? (string)$settings['vote_own_permission_label'] : null,
                    'vote_down_permission_label' => isset($settings['vote_down_permission_label']) ? (string)$settings['vote_down_permission_label'] : null,
                    'form_title' => isset($settings['form_title']) ? (string)$settings['form_title'] : null,
                    'form_options' => isset($settings['form_options']) ? $settings['form_options'] : null,
                    'form_default_value' => isset($settings['form_default_value']) ? $settings['form_default_value'] : null,
                    'form_redo_msg' => isset($settings['form_redo_msg']) ? (string)$settings['form_redo_msg'] : null,
                    'form_redo_btn' => isset($settings['form_redo_btn']) ? (string)$settings['form_redo_btn'] : null,
                    'form_success_msg' => isset($settings['form_success_msg']) ? (string)$settings['form_success_msg'] : null,
                ),
                'title' => isset($settings['title']) ? (string)$settings['title'] : null,
                'weight' => 99,
                'max_num_items' => 1, // Only 1 entry per entity should be created
            ),
            Sabai_Addon_Entity::FIELD_REALM_ALL
        );
    }
    
    public function onEntityUpdateBundlesSuccess($entityType, $bundles)
    {
        $this->onEntityCreateBundlesSuccess($entityType, $bundles);
    }
    
    public function onEntityDeleteBundlesSuccess($entityType, $bundles)
    {
        if ($entityType !== 'content') return;
        
        $criteria = $this->getModel()->createCriteria('Vote')->bundleId_in(array_keys($bundles));
        $this->getModel()->getGateway('Vote')->deleteByCriteria($criteria);
    }
    
    public function onEntityCreateEntity($bundle, &$values)
    {
        if ($bundle->entitytype_name !== 'content') {
            return;
        }
        
        // We need to create an empty entry for each voting type so that order by sql query returns 
        // results in the correct order.
        
        if (!empty($bundle->info['voting_tags'])) {
            foreach (array_keys($bundle->info['voting_tags']) as $tag_name) {
                $field_name = 'voting_' . $bundle->name . '_' . $tag_name;
                if (!isset($values[$field_name])) {
                    $values[$field_name] = array();
                }
            }
        }
        if (!empty($bundle->info['voting_updown'])) {
            if (!isset($values['voting_updown'])) {
                $values['voting_updown'] = array();
            }
        }
    }
    
    public function onEntityRenderEntities($bundle, $entities, $displayMode)
    {
        if ($bundle->entitytype_name !== 'content'
            || $this->_application->getUser()->isAnonymous()
        ) {
            return;
        }
        
        if ($displayMode === 'full') {   
            $votes = $this->getModel()->getGateway('Vote')->getVotes($bundle->entitytype_name, array_keys($entities), $this->_application->getUser()->id);
            foreach ($votes as $tag => $_votes) {
                foreach ($_votes as $entity_id => $value) {
                    $entities[$entity_id]->data['voting_' . $tag . '_voted'] = $value;
                }
            }
        } elseif ($displayMode === 'favorited') {
            $votes = $this->getModel()->getGateway('Vote')->getVotes($bundle->entitytype_name, array_keys($entities), $this->_application->getUser()->id, array('favorite'));
            if (!empty($votes['favorite'])) {
                foreach ($votes['favorite'] as $entity_id => $value) {
                    $entities[$entity_id]->data['voting_favorite_voted'] = $value;
                }
            }
        } elseif ($displayMode === 'flagged') {
            foreach ($this->getModel('Vote')->tag_is('flag')->entityId_in(array_keys($entities))->fetch()->with('User') as $flag) {
                $entities[$flag->entity_id]->data['voting_flags'][] = $flag;
            }
        }
    }
    
    public function getFieldByTag($tag)
    {
        return $this->_application->getModel('FieldConfig', 'Entity')->name_is('voting_' . $tag)->fetchOne();
    }
    
    public function onEntityRenderContentHtml(Sabai_Addon_Entity_Model_Bundle $bundle, Sabai_Addon_Entity_IEntity $entity, $displayMode, $id, &$classes, &$links)
    {
        if ($displayMode === 'preview') return;
        
        if ($this->_application->getUser()->isAnonymous()) {
            return;
        }
        
        if (!empty($bundle->info['voting_flag'])) {
            if ($displayMode === 'full') {
                if ($this->_application->HasPermission($entity->getBundleName() . '_voting_flag')) {
                    $links['voting_flag'] = $this->_application->LinkToModal(
                        isset($bundle->info['voting_flag']['button_label']) ? $bundle->info['voting_flag']['button_label'] : __('Flag', 'sabai'),
                        $this->_application->Entity_Url($entity, '/vote/flag/form', array('update_target_id' => $id)),
                        array('width' => 470, 'icon' => 'flag', 'active' => !empty($entity->data['voting_flag_voted'])),
                        array('title' => sprintf(isset($bundle->info['voting_flag']['button_title']) ? $bundle->info['voting_flag']['button_title'] : __('Flag this %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true)))
                    );
                }
            }
        
            if (($flag_count = $entity->getSingleFieldValue('voting_flag', 'count'))
                && $this->_application->HasPermission($entity->getBundleName() . '_manage')
            ) {
                // Let the moderators know that this content has been flagged
                $classes[] = 'sabai-voting-content-flagged';
                $entity->data['content_labels']['flagged'] = array(
                    'label' => __('Flagged', 'sabai'),
                    'title' => $title = sprintf(__('This post has %d flags', 'sabai'), $flag_count),
                    'icon' => 'flag',
                    'class' => 'sabai-voting-flagged',
                );
                $entity->data['content_icons']['flagged'] = array(
                    'title' => $title,
                    'icon' => 'flag',
                    'class' => 'sabai-voting-flagged',
                );
            }
        }
        
        if (!empty($bundle->info['voting_helpful']['button_enable'])) {
            if ($displayMode === 'full') {
                if (empty($entity->data['voting_helpful_voted'])) {
                    $title = sprintf(isset($bundle->info['voting_helpful']['button_on_title']) ? $bundle->info['voting_helpful']['button_on_title'] : __('Vote for this %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true));
                } else {
                    $title = sprintf(isset($bundle->info['voting_helpful']['button_off_title']) ? $bundle->info['voting_helpful']['button_off_title'] : __('Unvote for this %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true));
                }
                $links['voting_helpful'] = $this->_application->Voting_RenderVoteLink($entity, array(
                    'label' => isset($bundle->info['voting_helpful']['button_label']) ? $bundle->info['voting_helpful']['button_label'] : __('Vote', 'sabai'),
                    'title' => $title,
                    'active' => !empty($entity->data['voting_helpful_voted']),
                    'icon' => isset($bundle->info['voting_helpful']['icon']) ? $bundle->info['voting_helpful']['icon'] : 'thumbs-up',
                ));
            }
        }
        
        if (!empty($bundle->info['voting_favorite']['button_enable'])) {
            if ($displayMode === 'full' || $displayMode === 'favorited') {
                if (empty($entity->data['voting_favorite_voted'])) {
                    $title = sprintf(isset($bundle->info['voting_favorite']['button_on_title']) ? $bundle->info['voting_favorite']['button_on_title'] : __('Bookmark this %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true));
                } else {
                    $title= sprintf(isset($bundle->info['voting_favorite']['button_off_title']) ? $bundle->info['voting_favorite']['button_off_title'] : __('Unbookmark this %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true));
                }
                $links['voting_favorite'] = $this->_application->Voting_RenderVoteLink($entity, array(
                    'tag' => 'favorite',
                    'label' => isset($bundle->info['voting_favorite']['button_label']) ? $bundle->info['voting_favorite']['button_label'] : __('Bookmark', 'sabai'),
                    'title' => $title,
                    'active' => !empty($entity->data['voting_favorite_voted']),
                    'icon' => isset($bundle->info['voting_favorite']['icon']) ? $bundle->info['voting_favorite']['icon'] : 'bookmark',
                ));
            }
        }
    }
    
    public function recalculateEntityVotes(Sabai_Addon_Entity_IEntity $entity, $tag, $name, $update = true)
    {
        $results = $this->getModel()->getGateway('Vote')
            ->getResults($entity->getType(), $entity->getId(), $tag);
        
        // Calculate results
        if (empty($results[$name]['count'])) {
            $results[$name] = array(
                'count' => 0,
                'sum' => 0.00,
                'last_voted_at' => 0,
            ) + (array)@$results[$name]; 
        }
        if ($results[$name]['count'] && $results[$name]['sum']) {
            $results[$name]['average'] = round($results[$name]['sum'] / $results[$name]['count'], 2);
        } else {
            $results[$name]['average'] = 0.00;
        }

        $values = array();
        foreach ($results as $name => $result) {
            $values[] = array('name' => $name) + $result;
        }

        if ($update) {
            // Update voting fields of the entity
            $this->_application->getAddon('Entity')->updateEntity($entity, array('voting_' . $tag => $values));
        }
        
        return $results[$name];
    }
    
    public function deleteEntityVotes($entityId, $tag, $commit = true)
    {
        if ($entityId instanceof Sabai_Addon_Entity_IEntity) {
            $entityId = $entityId->getId();
        }
        $this->getModel('Vote')
            ->entityId_in((array)$entityId)
            ->tag_is($tag)
            ->fetch()
            ->delete($commit);
    }

    public function onFormBuildContentAdminListPosts(&$form, &$storage)
    {
        $this->_onFormBuildContentAdminListPosts($form);
    }
    
    public function onFormBuildContentAdminListChildPosts(&$form, &$storage)
    {
        $this->_onFormBuildContentAdminListPosts($form);
    }
    
    private function _onFormBuildContentAdminListPosts(&$form)
    {
        $has_voting = false;
        if (!empty($form['#bundle']->info['voting_updown'])) {
            $voting_updown = $has_voting = true;
        }
        if (!empty($form['#bundle']->info['voting_favorite'])) {
            $voting_favorite = $has_voting = true;
        }
        if (!empty($form['#bundle']->info['voting_flag'])) {
            $voting_flag = $has_voting = true;
        }
        if (!empty($form['#bundle']->info['voting_rating'])) {
            $voting_rating = $has_voting = true;
        }
        
        if (!$has_voting) return;

        if (!empty($voting_updown)) {
            $title = isset($form['#bundle']->info['voting_updown']['title']) ? $form['#bundle']->info['voting_updown']['title'] : __('Votes', 'sabai');
            $form['entities']['#header']['vote'] = array(
                'order' => 30,
                'label' => '<i title="'. Sabai::h($title) .'" class="sabai-icon-large sabai-icon-thumbs-up"></i>',
            );
            $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]['action']['#options']['clear_votes'] = sprintf(__('Clear %s', 'sabai'), $title);
        }
        if (!empty($voting_flag)) {
            $title = isset($form['#bundle']->info['voting_flag']['title']) ? $form['#bundle']->info['voting_flag']['title'] : __('Flags', 'sabai');
            $form['entities']['#header']['flag'] = array(
                'order' => 32,
                'label' => '<i title="'. Sabai::h($title) .'" class="sabai-icon-large sabai-icon-flag"></i>',
            );
            $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]['action']['#options']['clear_flags'] = sprintf(__('Clear %s', 'sabai'), $title);
            
            $form['#filters']['voting_flag'] = array(
                'default_option_label' => __('Flagged / Unflagged', 'sabai'),
                'options' => array(1 => __('Flagged', 'sabai'), 2 => __('Unflagged', 'sabai')),
                'order' => 50,
            );     
        }
        if (!empty($voting_favorite)) {
            $title = isset($form['#bundle']->info['voting_favorite']['title']) ? $form['#bundle']->info['voting_favorite']['title'] : __('Favorites', 'sabai');
            $form['entities']['#header']['favorite'] = array(
                'order' => 31,
                'label' => isset($form['#bundle']->info['voting_favorite']['icon'])
                    ? '<i title="'. Sabai::h($title) .'" class="sabai-icon-large sabai-icon-' . $form['#bundle']->info['voting_favorite']['icon'] .'"></i>' 
                    : '<i title="'. Sabai::h($title) .'" class="sabai-icon-large sabai-icon-bookmark"></i>',
            );
            $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]['action']['#options']['clear_favorites'] = sprintf(__('Clear %s', 'sabai'), $title);
        }
        if (!empty($voting_rating)) {
            $title = isset($form['#bundle']->info['voting_rating']['title']) ? $form['#bundle']->info['voting_rating']['title'] : __('Ratings', 'sabai');
            $form['entities']['#header']['rating'] = array(
                'order' => 33,
                'label' => '<i title="'. Sabai::h($title) .'" class="sabai-icon-large sabai-icon-star"></i>',
            );
            $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]['action']['#options']['clear_ratings'] = sprintf(__('Clear %s', 'sabai'), $title);
        }
        if (empty($form['entities']['#options'])) {
            return;
        }
        foreach ($form['entities']['#options'] as $entity_id => $data) {
            $entity = $data['#entity'];
            $entity_path = $form['#bundle']->getPath() . '/' . $entity->getId();
            if (!empty($voting_updown)) {
                $form['entities']['#options'][$entity_id]['vote'] = ($vote_count = $entity->getSingleFieldValue('voting_updown', 'count'))
                    ? $this->_application->LinkTo(
                          sprintf('%d (%d)', $vote_count, ($vote_sum = $entity->getSingleFieldValue('voting_updown', 'sum'))),
                          $this->_application->Url($entity_path . '/voting_updown'),
                          array(),
                          array('title' => sprintf(_n('%d vote (score: %d)', '%d votes (score: %d)', $vote_count, 'sabai'), $vote_count, $vote_sum))
                       )
                    : '0 (0)';
            }
            if (!empty($voting_favorite)) {
                $form['entities']['#options'][$entity_id]['favorite'] = ($favorite_count = $entity->getSingleFieldValue('voting_favorite', 'count'))
                    ? $this->_application->LinkTo(
                          $favorite_count,
                          $this->_application->Url($entity_path . '/voting_favorite'),
                          array(),
                          array('title' => sprintf(_n('%d favorite', '%d favorites', $favorite_count, 'sabai'), $favorite_count))
                       )
                    : 0;
            }
            if (!empty($voting_flag)) {
                $form['entities']['#options'][$entity_id]['flag'] = ($flag_count = $entity->getSingleFieldValue('voting_flag', 'count'))
                    ? $this->_application->LinkTo(
                          sprintf('%d (%d)', $flag_count, ($flag_sum = $entity->getSingleFieldValue('voting_flag', 'sum'))),
                          $this->_application->Url($entity_path . '/voting_flag'),
                          array(),
                          array('title' => sprintf(_n('%d flag (spam score: %d)', '%d flags (spam score: %d)', $flag_count, 'sabai'), $flag_count, $flag_sum))
                      )
                    : '0 (0)';
            }
            if (!empty($voting_rating)) {
                $form['entities']['#options'][$entity_id]['rating'] = ($rating_count = $entity->getSingleFieldValue('voting_rating', 'count', ''))
                    ? $this->_application->LinkTo(
                          sprintf('%d (%.2f)', $rating_count, ($rating_avg = $entity->getSingleFieldValue('voting_rating', 'average', ''))),
                          $this->_application->Url($entity_path . '/voting_rating'),
                          array(),
                          array('title' => sprintf(__('%.2f out of 5 stars', 'sabai'), $rating_avg))
                       )
                    : 0;
            }
        }

        $form['#submit'][0][] = array($this, 'updateEntities');
    }
    
    public function updateEntities(Sabai_Addon_Form_Form $form)
    {
        if (!empty($form->values['entities'])) {
            switch ($form->values['action']) {
                case 'clear_flags':
                    $this->_application->Voting_DeleteVotes($form->values['entities'], 'flag');
                    break;
                case 'clear_votes':
                    $this->_application->Voting_DeleteVotes($form->values['entities'], 'updown');
                    break;
                case 'clear_favorites':
                    $this->_application->Voting_DeleteVotes($form->values['entities'], 'favorite');
                    break;
                case 'clear_ratings':
                    $this->_application->Voting_DeleteVotes($form->values['entities'], 'rating');
                    break;
            }
        }
    }
    
    public function onContentPostsTrashed($entities)
    {
        // Clear flags of trashed posts
        $this->_application->Voting_DeleteVotes(array_keys($entities), 'flag');
    }
    
    public function onCommentFlaggedAsSpam($comment, $entity)
    {
        $vote_comment = sprintf(
            __('Comment posted by %s on %s has been marked as spam (spam score: %d, flag count: %d)', 'sabai'),
            $comment->User->name,
            $this->_application->DateTime($comment->published_at),
            $comment->flag_sum,
            $comment->flag_count
        );
        $this->_application->Voting_CastVote($entity, 'flag', self::FLAG_VALUE_OTHER, array('comment' => $vote_comment, 'system' => true));
    }
    
    public function formGetFieldTypes()
    {
        return array('voting_rateit');
    }

    public function formGetField($type)
    {
        return new Sabai_Addon_Voting_FormField($this, $type);
    }
    
    public function onContentAdminPostsUrlParamsFilter(&$urlParams, $context, $bundle)
    {
        if (!empty($bundle->info['voting_flag'])) {
            if ($voting_flag = $context->getRequest()->asInt('voting_flag')){
                $urlParams['voting_flag'] = $voting_flag;
            }
        }
    }
    
    public function onContentAdminPostsQuery($context, $bundle, $query, $countQuery, $sort, $order)
    {
        if (!empty($bundle->info['voting_flag'])) {
            if ($voting_flag = $context->getRequest()->asInt('voting_flag')){
                switch ($voting_flag) {
                    case 1:
                        $query->fieldIsGreaterThan('voting_flag', 0, 'count');
                        $countQuery->fieldIsGreaterThan('voting_flag', 0, 'count');
                    break;
                    case 2:
                        $query->fieldIsNull('voting_flag', 'count');
                        $countQuery->fieldIsNull('voting_flag', 'count');
                    break;
                }
            }
        }
    }
}
