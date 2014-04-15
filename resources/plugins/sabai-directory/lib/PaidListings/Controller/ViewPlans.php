<?php
abstract class Sabai_Addon_PaidListings_Controller_ViewPlans extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        // Init variables
        $this->_submitable = false;
        $this->_successFlash = __('Settings saved.', 'sabai-directory');
        $sortable_headers = array('name' => 'name', 'weight' => 'weight');
        $sort = $context->getRequest()->asStr('sort', 'weight', array_keys($sortable_headers));
        $order = $context->getRequest()->asStr('order', 'ASC', array('ASC', 'DESC'));
        $url_params = array('sort' => $sort, 'order' => $order);
        // Init plan type filters and current filter
        $filters = array('' => $this->LinkToRemote(__('All', 'sabai-directory'), $context->getContainer(), $this->Url($context->getRoute(), $url_params), array(), array('class' => 'sabai-btn sabai-btn-mini')));
        $plan_types = $this->_getPlanTypes($context);
        foreach ($plan_types as $type => $type_info) {
            $filters[$type] = $this->LinkToRemote($type_info['label'], $context->getContainer(), $this->Url($context->getRoute(), array('type' => $type) + $url_params), array(), array('class' => 'sabai-btn sabai-btn-mini'));
        }
        $filter = $context->getRequest()->asStr('type', '', array_keys($filters));
        $filters[$filter]->setAttribute('class', $filters[$filter]->getAttribute('class') . ' sabai-active');
        $url_params['type'] = $filter;
        
        // Paginate plans
        $plans = $this->getModel('Plan', 'PaidListings');
        if ($filter) {
            $plans->type_is($filter);
        } else {
            $plans->type_in(array_keys($filters));
        }
        $pager = $plans->paginate(20, $sortable_headers[$sort], $order)
            ->setCurrentPage($url_params[Sabai::$p] = $context->getRequest()->asInt(Sabai::$p, 1));
        
        // Init form
        $form = array(
            'plans' => array(
                '#type' => 'tableselect',
                '#options' => array(),
                '#disabled' => true,
                '#header' => array(
                    'name' => __('Plan Name', 'sabai-directory'),
                    'type' => __('Plan Type', 'sabai-directory'),
                    'description' => __('Description', 'sabai-directory'),
                    'price' => __('Price', 'sabai-directory'),
                    'status' => __('Status', 'sabai-directory'),
                ),
                '#sortable' => true,
            ),
        );
        
        // Set sortable headers
        $this->_makeTableSortable($context, $form['plans'], array_keys($sortable_headers), array(), $sort, $order, $url_params);

        foreach ($pager->getElements() as $plan) {
            $links = array(
                $this->LinkToModal(__('Edit', 'sabai-directory'), $this->Url($this->_getPlanPath($context, $plan, '/edit'), $url_params), array('width' => 720), array('title' => __('Edit Plan', 'sabai-directory'))),
                $this->LinkToModal(__('Delete', 'sabai-directory'), $this->Url($this->_getPlanPath($context, $plan, '/delete'), $url_params), array('width' => 470), array('title' => __('Delete Plan', 'sabai-directory'))),
            );
            $form['plans']['#options'][$plan->id] = array(
                'name' => '<strong class="sabai-row-title">' . $this->LinkToModal($plan->name, $this->Url($this->_getPlanPath($context, $plan), $url_params), array(), array('title' => __('Edit Plan', 'sabai-directory'))) . '</strong><div class="sabai-row-action">' . $this->Menu($links) . '</div>',
                'type' => $plan_types[$plan->type]['title'],
                'description' => Sabai::h($plan->description),
                'price' => $this->PaidListings_MoneyFormat($plan->price, $this->_getCurrency($context)),
                'status' => $plan->active ? '<span class="sabai-label sabai-label-success">' . __('Active', 'sabai-directory') . '</span>' : '<span class="sabai-label sabai-label-important">' . __('Inactive', 'sabai-directory') . '</span>',
            );
        }
        
        // Add URL params as hidden
        foreach ($url_params as $key => $value) {
            $form[$key] = array(
                '#type' => 'hidden',
                '#value' => $value,
            );
        }
        
        $context->addTemplate('paidlistings_plans')
            ->setAttributes(array(
                'links' => array(
                    $this->_getAddPlanLink($context, $url_params),
                ),
                'filters' => $filters,
                'paginator' => $pager,
                'url_params' => $url_params,
            ));
        
        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
       $context->setSuccess($this->Url($context->getRoute(), $context->url_params));
    }
    
    protected function _getAddPlanLink(Sabai_Context $context, array $params)
    {
        return $this->LinkToModal(__('Add Plan', 'sabai-directory'), $this->Url($context->getRoute() . 'add', $params), array('icon' => 'plus', 'width' => 720), array('class' => 'sabai-btn sabai-btn-success sabai-btn-small', 'title' => __('Add Plan', 'sabai-directory')));
    }
    
    protected function _getPlanPath(Sabai_Context $context, Sabai_Addon_PaidListings_Model_Plan $plan, $path = '')
    {
        return $context->getRoute() . $plan->id . $path;
    }
    
    abstract protected function _getPlanTypes(Sabai_Context $context);
    abstract protected function _getCurrency(Sabai_Context $context);
}
