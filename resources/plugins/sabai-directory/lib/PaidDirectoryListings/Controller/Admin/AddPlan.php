<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/PaidListings/Controller/AddPlan.php';

class Sabai_Addon_PaidDirectoryListings_Controller_Admin_AddPlan extends Sabai_Addon_PaidListings_Controller_AddPlan
{
    protected function _getPlanTypes(Sabai_Context $context)
    {
        return $this->getAddon()->getPlanTypes();
    }
    
    protected function _getCurrency(Sabai_Context $context)
    {
        return $this->getAddon()->getConfig('paypal', 'currency');
    }
}