<?php
abstract class Sabai_Addon_PaidListings_Controller_DeletePlan extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        // Fetch plan to edit
        $context->plan = $this->getModel('Plan', 'PaidListings')->fetchById($this->_getPlanId($context));
        if (!$context->plan) {
            return false;
        }
        $this->_submitButtons['submit'] = array(
            '#value' => __('Delete Plan', 'sabai-directory'),
            '#btn_type' => 'danger',
        );
        $url_params = array('sort' => $context->getRequest()->asStr('sort'), 'order' => $context->getRequest()->asStr('order'), 'type' => $context->getRequest()->asStr('type'));
        $this->_cancelUrl = $this->_getPlansUrl($context, $url_params);
        $form = array();
        $form['#header'][] = sprintf(
            '<div class="sabai-warning">%s</div>',
            __('Are you sure you want to delete this plan? This cannot be undone.', 'sabai-directory')
        );
        
        // Add URL params as hidden
        foreach ($url_params as $key => $value) {
            $form[$key] = array(
                '#type' => 'hidden',
                '#value' => $value,
            );
        }

        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $context->plan->markRemoved()->commit();
        $this->doEvent('PaidListingsDeletePlanSuccess', array($context->plan));
        $context->setSuccess($this->_cancelUrl);
    }
    
    abstract protected function _getPlansUrl(Sabai_Context $context, array $params);
    /**
     * @return intger
     */
    abstract protected function _getPlanId(Sabai_Context $context);
}