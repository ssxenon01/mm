<?php
class Sabai_Addon_Comment extends Sabai_Addon
    implements Sabai_Addon_System_IMainRouter,
               Sabai_Addon_System_IAdminRouter,
               Sabai_Addon_Field_ITypes,
               Sabai_Addon_Content_IPermissions
{
    const VERSION = '1.2.31', PACKAGE = 'sabai';
    const POST_STATUS_PUBLISHED = 0, POST_STATUS_HIDDEN = 1, POST_STATUS_FEATURED = 2;
    const VOTE_FLAG_VALUE_SPAM = 5, VOTE_FLAG_VALUE_OFFENSIVE = 6, VOTE_FLAG_VALUE_OFFTOPIC = 2, VOTE_FLAG_VALUE_OTHER = 0;
                
    public function isUninstallable($currentVersion)
    {
        return false;
    }
    
    /* Start implementation of Sabai_Addon_System_IMainRouter */
    
    public function systemGetMainRoutes()
    {
        $routes = array();
        foreach ($this->_application->getModel('FieldConfig', 'Entity')->type_is('comment_comments')->fetch()->with('Fields', 'Bundle') as $field_config) {                
            foreach ($field_config->Fields as $field) {
                $base_path = empty($field->Bundle->info['permalink_path'])
                    ? $field->Bundle->getPath() . '/:entity_id'
                    : $field->Bundle->info['permalink_path'] . '/:slug';
                $routes[$base_path . '/comments'] = array(
                    'controller' => 'Comments',
                    'title_callback' => true,
                    'callback_path' => 'comments',
                );
                $routes[$base_path . '/comments/add'] = array(
                    'controller' => 'AddComment',
                    'callback_path' => 'add_comment',
                    'access_callback' => true,
                    'title_callback' => true,
                );
                $routes[$base_path . '/comments/:comment_id'] = array(
                    'controller' => 'Comment',
                    'title_callback' => true,
                    'callback_path' => 'comment',
                    'access_callback' => true,
                    'format' => array(':comment_id' => '\d+'),
                );
                $routes[$base_path . '/comments/:comment_id/edit'] = array(
                    'controller' => 'EditComment',
                    'title_callback' => true,
                    'callback_path' => 'edit_comment',
                    'access_callback' => true,
                );
                $routes[$base_path . '/comments/:comment_id/delete'] = array(
                    'controller' => 'DeleteComment',
                    'title_callback' => true,
                    'callback_path' => 'delete_comment',
                    'access_callback' => true,
                );
                $routes[$base_path . '/comments/:comment_id/hide'] = array(
                    'controller' => 'HideComment',
                    'title_callback' => true,
                    'callback_path' => 'hide_comment',
                    'access_callback' => true,
                );
                $routes[$base_path . '/comments/:comment_id/vote'] = array(
                    'controller' => 'VoteComment',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'callback_path' => 'vote_comment',
                    'access_callback' => true,
                );
                $routes[$base_path . '/comments/:comment_id/flag'] = array(
                    'controller' => 'FlagComment',
                    'callback_path' => 'flag_comment',
                    'access_callback' => true,
                );
            }
        }

        return $routes;
    }

    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'comment':
                if ((!$id = $context->getRequest()->asStr('comment_id'))
                    || (!$context->comment = $this->getModel('Post')->fetchById($id))
                    || ($context->comment->isHidden() && !$this->_application->HasPermission($context->entity->getBundleName() . '_manage'))
                ) {
                    return false;
                }
                $context->is_comment_owner = $context->comment->user_id === $this->_application->getUser()->id;
                return true;
            case 'add_comment':
                if ($this->_application->getUser()->isAnonymous()) {
                    $context->setUnauthorizedError($this->_application->Entity_Url($context->entity, '/comments/add'));
                    return false;
                }
                return $this->_application->HasPermission($context->entity->getBundleName() . '_comment_add')
                    || $context->entity->getAuthorId() === $this->_application->getUser()->id; // Owner of entity can always add comment
            case 'edit_comment':
                return $this->_application->HasPermission($context->entity->getBundleName() . '_comment_edit_any')
                    || ($context->is_comment_owner && $this->_application->HasPermission($context->entity->getBundleName() . '_comment_edit_own'));
            case 'delete_comment':
                return $this->_application->getUser()->isAdministrator()
                    || ($context->is_comment_owner && $this->_application->HasPermission($context->entity->getBundleName() . '_comment_delete_own'));
            case 'hide_comment':
                return $this->_application->HasPermission($context->entity->getBundleName() . '_manage');
            case 'vote_comment':
                if ($context->comment->vote_disabled) {
                    return false;
                }
                return $this->_application->HasPermission($context->entity->getBundleName() . '_comment_vote')
                    && (!$context->is_comment_owner
                           || $this->_application->HasPermission($context->entity->getBundleName() . '_comment_vote_own') // requires additional permission to vote for own comment
                       );
            case 'flag_comment':
                if ($context->comment->flag_disabled) {
                    return false;
                }
                return $this->_application->HasPermission($context->entity->getBundleName() . '_comment_flag');
        }
    }

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'comments':
                return sprintf(__('Comments for "%s"', 'sabai'), $context->entity->getTitle());
            case 'comment':
                return mb_strimwidth(strip_tags(strtr($context->comment->body_html, array("\r" => '', "\n" => ''))), 0, 100, '...');
            case 'hide_comment':
                return $context->comment->isHidden() ? __('Unhide Comment', 'sabai') : __('Hide Comment', 'sabai');
            case 'add_comment':
                return __('Add Comment', 'sabai');
            case 'edit_comment':
                return __('Edit Comment', 'sabai');
            case 'delete_comment':
                return __('Delete Comment', 'sabai');
        }
    }

    /* End implementation of Sabai_Addon_System_IMainRouter */
    
    /* Start implementation of Sabai_Addon_System_IAdminRouter */
    
    public function systemGetAdminRoutes()
    {
        $routes = array();
        foreach ($this->_application->getModel('FieldConfig', 'Entity')->type_is('comment_comments')->fetch()->with('Fields', 'Bundle') as $field_config) {                
            foreach ($field_config->Fields as $field) {
                $routes[$field->Bundle->getPath() . '/:entity_id/comments'] = array(
                    'controller' => 'ListComments',
                    'title_callback' => true,
                    'type' => Sabai::ROUTE_TAB,
                    'ajax' => 1,
                    'weight' => 3,
                    'callback_path' => 'comments',
                );
                $routes[$field->Bundle->getPath() . '/:entity_id/comments/:comment_id'] = array(
                    'controller' => 'EditComment',
                    'format' => array(':comment_id' => '\d+'),
                    'access_callback' => true,
                    'title_callback' => true,
                    'callback_path' => 'comment',
                );
                $routes[$field->Bundle->getPath() . '/:entity_id/comments/:comment_id/votes'] = array(
                    'controller' => 'Votes',
                    'callback_path' => 'votes',
                    'title_callback' => true,
                    'ajax' => 1,
                );
                $routes[$field->Bundle->getPath() . '/:entity_id/comments/:comment_id/flags'] = array(
                    'controller' => 'Flags',
                    'callback_path' => 'flags',
                    'title_callback' => true,
                    'ajax' => 1,
                );
            }
        }
        $routes['/settings/comment'] = array(
            'controller' => 'Settings',
            'title_callback' => true,
            'callback_path' => 'settings',
        );

        return $routes;
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'comment':
                if ((!$id = $context->getRequest()->asInt('comment_id'))
                    || (!$comment = $this->getModel('Post')->fetchById($id))
                ) {
                    return false;
                }
                $context->comment = $comment;
                return true;
        }
    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'comments':
                if ($titleType !== Sabai::ROUTE_TITLE_TAB) {
                    return __('Comments', 'sabai');
                }
                $entity_id = $context->entity->getId();
                $comment_counts = $this->getModel()->getGateway('Post')->getCountByEntities(array($entity_id), true);
                return empty($comment_counts[$entity_id]) ? __('Comments', 'sabai') : sprintf(__('Comments (%d)', 'sabai'), $comment_counts[$entity_id]);
            case 'comment':
                return $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT
                    ? __('Edit', 'sabai')
                    : mb_strimwidth(strip_tags(strtr($context->comment->body_html, array("\r" => '', "\n" => ''))), 0, 100, '...');
            case 'votes':
                return $titleType !== Sabai::ROUTE_TITLE_TAB || !$context->comment->vote_count
                    ? __('Votes', 'sabai')
                    : sprintf(__('%s (%d)', 'sabai'), $title, $context->comment->vote_count);
            case 'flags':
                return $titleType !== Sabai::ROUTE_TITLE_TAB || !$context->comment->flag_count
                    ? __('Flags', 'sabai')
                    : sprintf(__('%s (%d)', 'sabai'), $title, $context->comment->flag_count);
            case 'settings':
                return __('Comment Spam Settings', 'sabai');
        }
    }

    /* End implementation of Sabai_Addon_System_IAdminRouter */
    
    /* Start implementation of Sabai_Addon_Field_ITypes */

    public function fieldGetTypeNames()
    {
        return array('comment_comments');
    }

    public function fieldGetType($name)
    {
        return new Sabai_Addon_Comment_FieldType($this, $name);
    }

    /* End implementation of Sabai_Addon_Field_ITypes */
    
    /* Start implementation of Sabai_Addon_Content_IPermissions */

    public function contentGetPermissions(Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        if (!isset($bundle->info['comment_comments'])) {
            return array();
        }
        return array(
            'comment_add' => __('Add comment to %s', 'sabai'),
            'comment_edit_own' => __('Edit own comments on %s', 'sabai'),
            'comment_edit_any' => __('Edit any comment on %s', 'sabai'),
            'comment_delete_own' => __('Delete own comments on %s', 'sabai'),
            'comment_vote' => __('Vote up comments on %s', 'sabai'),
            'comment_vote_own' => __('Vote up own comments on %s', 'sabai'),
            'comment_flag' => __('Flag comments on %s', 'sabai'),
        );
    }
    
    public function contentGetDefaultPermissions(Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        if (!isset($bundle->info['comment_comments'])) {
            return array();
        }
        return array('comment_add', 'comment_edit_own', 'comment_delete_own', 'comment_vote', 'comment_flag');
    }

    /* End implementation of Sabai_Addon_Content_IPermissions */
    
    public function onEntityCreateBundlesSuccess($entityType, $bundles)
    {
        if ($entityType !== 'content') return;

        $reload_routes = false;
        foreach ($bundles as $bundle) {
            if (!isset($bundle->info['comment_comments'])) {
                continue;
            }
            
            $settings = $bundle->info['comment_comments'];
            $reload_routes = true;
            $this->_application->getAddon('Entity')->createEntityField(
                $bundle,
                'comment_comments',
                array(
                    'type' => 'comment_comments',
                    'settings' => array(),
                    'title' => isset($settings['title']) ? $settings['title'] : __('Comments', 'sabai'),
                    'weight' => 99,
                    'max_num_items' => 1, // Only 1 entry per entity should be created
                ),
                Sabai_Addon_Entity::FIELD_REALM_ALL
            );
        }
        if ($reload_routes) {
            // Reload system routing tables to reflect changes
            $this->_application->getAddon('System')->reloadRoutes($this)->reloadRoutes($this, true);
        }
    }
    
    public function onEntityUpdateBundlesSuccess($entityType, $bundles)
    {
        $this->onEntityCreateBundlesSuccess($entityType, $bundles);
    }
    
    public function onEntityDeleteBundlesSuccess($entityType, $bundles)
    {        
        if ($entityType !== 'content') return;

        $bundle_ids = array();
        foreach ($bundles as $bundle) {
            $bundle_ids[] = $bundle->id;
        }
        $criteria = $this->getModel()->createCriteria('Post')->entityBundleId_in($bundle_ids);
        $this->getModel()->getGateway('Post')->deleteByCriteria($criteria);
    }
    
    public function onEntityRenderEntities($bundle, $entities, $displayMode)
    {
        if ($displayMode !== 'full'
            || $bundle->entitytype_name !== 'content'
            || !isset($bundle->info['comment_comments'])
        ) {
            return;
        }
        
        $entity_ids = array_keys($entities);
        $comment_count = $this->getModel()->getGateway('Post')->getCountByEntities($entity_ids, $this->_application->getUser()->isAdministrator());
        $comment_ids = $comment_entity_ids = array();
        foreach ($this->getModel('Post')->getFeaturedByEntities($entity_ids) as $comment) {
            $entities[$comment->entity_id]->data['comment_comments'][$comment->id] = $comment->toArray();
            $comment_ids[] = $comment->id;
            $comment_entity_ids[$comment->id] = $comment->entity_id;
        }
        // Entities without featured comments. This could be that the entities have hidden comments only
        foreach ($comment_count as $comment_entity_id => $_comment_count) {
            $entities[$comment_entity_id]->data['comment_count'] = $_comment_count;
        }
        // Fetch comments already voted by the current user
        if (!$this->_application->getUser()->isAnonymous()) {
            foreach ($this->getModel()->getGateway('Vote')->getPostsVoted($comment_ids, $this->_application->getUser()->id) as $voted_comment_id) {
                $entities[$comment_entity_ids[$voted_comment_id]]->data['comment_comments_voted'][] = $voted_comment_id;
            }
        }
    }
    
    public function onFormBuildContentAdminListposts(&$form, &$storage)
    {
        $this->_onFormBuildContentAdminListposts($form);
    }
    
    public function onFormBuildContentAdminListchildposts(&$form, &$storage)
    {
        $this->_onFormBuildContentAdminListposts($form);
    }
    
    private function _onFormBuildContentAdminListposts(&$form)
    {
        if (!isset($form['#bundle']->info['comment_comments'])) {
            return;
        }
        $form['entities']['#header']['comments'] = array(
            'order' => 25,
            'label' => '<i title="'. Sabai::h(__('Comments', 'sabai')) .'" class="sabai-icon-large sabai-icon-comment"></i>',
        );
        $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]['action']['#options']['clear_comments'] = __('Clear comments', 'sabai');
        if (empty($form['entities']['#options'])) {
            return;
        }
        $count = $this->getModel()->getGateway('Post')->getCountByEntities(array_keys($form['entities']['#options']), true);
        foreach (array_keys($form['entities']['#options']) as $entity_id) {
            $form['entities']['#options'][$entity_id]['comments'] = !empty($count[$entity_id])
                ? $this->_application->LinkTo($count[$entity_id], $form['#bundle']->getPath() . '/' . $entity_id . '/comments')
                : 0;
        }

        $form['#submit'][0][] = array($this, 'clearComments');
    }
    
    public function clearComments(Sabai_Addon_Form_Form $form)
    {
        if (empty($form->values['entities'])) return;
        
        $criteria = $this->getModel()->createCriteria('Post')->entityId_in($form->values['entities']);
        $this->getModel()->getGateway('Post')->deleteByCriteria($criteria);
    }
    
    public function getDefaultConfig()
    {
        return array(
            'spam' => array(
                'threshold' => 11,
                'auto_delete' => true,
                'delete_after' => 3,
            ),
            'show_login_link' => true,
        );
    }
    
    public function onSabaiRunCron($lastRunTimestamp, $logs)
    {
        if (!$this->_config['spam']['auto_delete']) {
            // Auto-delete spam not enabled
            return;
        }
        
        // Fetch comments marked as spam and hidden more than X days ago
        $days = $this->_config['spam']['delete_after'];
        $comments = $this->getModel('Post')
            ->status_is(self::POST_STATUS_HIDDEN)
            ->hiddenAt_isOrSmallerThan(time() - $days * 86400)
            ->fetch();
        $count = $comments->count();
        $comments->delete(true); 
        $logs[] = sprintf(__('Deleted %d comment spam(s)', 'sabai'), $count);
    }
    
    public function hasSettingsPage($currentVersion)
    {
        return array('url' => '/settings/comment', 'modal' => true);
    }
}
