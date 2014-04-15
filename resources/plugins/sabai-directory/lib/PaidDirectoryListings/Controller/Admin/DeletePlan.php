<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/PaidListings/Controller/DeletePlan.php';

class Sabai_Addon_PaidDirectoryListings_Controller_Admin_DeletePlan extends Sabai_Addon_PaidListings_Controller_DeletePlan
{  
    protected function _getPlanId(Sabai_Context $context)
    {        
        return $context->getRequest()->asInt('plan_id');
    }
    
    protected function _getPlansUrl(Sabai_Context $context, array $params)
    {
        return $this->Url('/paiddirectorylistings/plans', $params);
    }
}