<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/PaidListings/Controller/ViewPlans.php';

class Sabai_Addon_PaidDirectoryListings_Controller_Admin_Plans extends Sabai_Addon_PaidListings_Controller_ViewPlans
{
    protected function _doExecute(Sabai_Context $context)
    {
        parent::_doExecute($context);
        $context->clearTabs();
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