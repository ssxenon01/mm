<?php
class Sabai_Addon_Directory_Controller_Admin_ListingClaims extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        // Init variables
        $status_labels = $this->Directory_ClaimStatusLabels();
        $criteria = $this->getModel(null, 'Directory')->createCriteria('Claim')
            ->entityBundleName_is($context->bundle->name)
            ->status_in(array_keys($status_labels));
        $sortable_headers = array('date' => 'created');
        $sort = $context->getRequest()->asStr('sort', 'date', array_keys($sortable_headers));
        $order = $context->getRequest()->asStr('order', 'DESC', array('ASC', 'DESC'));
        $url_params = array('sort' => $sort, 'order' => $order);
        // Init entity ID
        if (($entity_id = $context->getRequest()->asInt('entity_id'))
            && $entity = $this->Entity_Entity('content', $entity_id, false)
        ) {
            $url_params['entity_id'] = $entity_id;
            $criteria->entityId_is($entity_id);
            $context->setInfo($entity->getTitle());
        }
        // Init status filters and current filter
        $filters = array('' => $this->LinkToRemote(__('All', 'sabai-directory'), $context->getContainer(), $this->Url($context->getRoute(), $url_params), array(), array('class' => 'sabai-btn sabai-btn-mini')));
        foreach ($status_labels as $status_name => $status_label) {
            $filters[$status_name] = $this->LinkToRemote($status_label, $context->getContainer(), $this->Url($context->getRoute(), array('status' => $status_name) + $url_params), array(), array('class' => 'sabai-btn sabai-btn-mini'));
        }
        $status = $context->getRequest()->asStr('status', '', array_keys($filters));
        $filters[$status]->setAttribute('class', $filters[$status]->getAttribute('class') . ' sabai-active');
        if ($status) {
            $url_params['status'] = $status;
            $criteria->status_is($status);
        }

        // Paginate claims
        $pager = $this->getModel('Claim')
            ->paginateByCriteria($criteria, 20, $sortable_headers[$sort], $order)
            ->setCurrentPage($context->getRequest()->asInt(Sabai::$p, 1));
        
        // Init form
        $form = array(
            '#bundle' => $context->bundle,
            'claims' => array(
                '#type' => 'tableselect',
                '#header' => array(
                    'id' => __('Claim ID', 'sabai-directory'),
                    'date' => __('Claim Date', 'sabai-directory'),
                    'listing' => __('Listing', 'sabai-directory'),
                    'user' => __('User', 'sabai-directory'),
                    'comment' => __('Comment', 'sabai-directory'),
                    'status' => __('Status', 'sabai-directory'),
                ),
                '#options' => array(),
                '#options_disabled' => array(),
                '#multiple' => true,
            ),
        );
        
        // Set submit buttons
        $this->_submitButtons = array(array('#value' => __('Delete', 'sabai-directory')));
        
        // Set sortable headers
        $this->_makeTableSortable($context, $form['claims'], array_keys($sortable_headers), array(), $sort, $order, $url_params);

        $claims = array();
        foreach ($pager->getElements()->with('User')->with('Entity') as $claim) {
            $form['claims']['#options'][$claim->id] = array(
                'id' => $this->LinkToModal('<strong class="sabai-row-title">' . $claim->getLabel() . '</strong>', $this->Url($context->getRoute() . $claim->id), array('no_escape' => true, 'width' => 470), array('title' => sprintf(__('Claim %s', 'sabai-directory'), $claim->getLabel()))),
                'date' => $this->Date($claim->created),
                'user' => $this->UserIdentityLinkWithThumbnailSmall($claim->User),
                'comment' => $claim->getSummary(100),
                'status' => sprintf('<span class="sabai-label %s">%s</span>', $claim->getStatusLabelClass(), $claim->getStatusLabel()),
                'listing' => $claim->Entity ? ($claim->Entity->isPublished() ? $this->LinkTo($claim->Entity->getTitle(), $this->Entity_Bundle($claim->Entity)->getPath() . '/' . $claim->Entity->getId()) : Sabai::h($claim->Entity->getTitle())) : '',
            );
            if (!in_array($claim->status, array('approved', 'rejected'))) {
                $form['claims']['#options_disabled'][] = $claim->id;
            }
            $claims[$claim->id] = $claim;
        }
        
        $context->addTemplate('directory_admin_listing_claims')
            ->setAttributes(array(
                'links' => array(),
                'filters' => $filters,
                'paginator' => $pager,
                'url_params' => $url_params,
                'claims' => $claims,
            ));
        
        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!empty($form->values['claims'])) {
            foreach ($form->values['claims'] as $claim_id) {
                if (!isset($context->claims[$claim_id])
                    || !in_array($context->claims[$claim_id]->status, array('approved', 'rejected'))
                ) {
                    continue;
                }
                $context->claims[$claim_id]->markRemoved();
            }
            $this->getModel(null, 'Directory')->commit();
        }
        $context->setSuccess();
    }
}
