<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/PaidListings/Controller/EditPlan.php';

class Sabai_Addon_PaidDirectoryListings_Controller_Admin_EditPlan extends Sabai_Addon_PaidListings_Controller_EditPlan
{  
    protected function _getPlanId(Sabai_Context $context)
    {        
        return $context->getRequest()->asInt('plan_id');
    }
   
    protected function _getPlansUrl(Sabai_Context $context, array $params)
    {
        return $this->Url('/paiddirectorylistings/plans', $params);
    }
    
    protected function _getPlanTypes(Sabai_Context $context)
    {
        return $this->getAddon()->getPlanTypes();
    }
    
    protected function _getCurrency(Sabai_Context $context)
    {
        return $this->getAddon()->getConfig('paypal', 'currency');
    }
}