<?php
require_once dirname(dirname(dirname(__FILE__))) . '/PaidListings/Controller/ViewOrders.php';

abstract class Sabai_Addon_PaidDirectoryListings_Controller_Orders extends Sabai_Addon_PaidListings_Controller_ViewOrders
{    
    protected function _viewOrders(Sabai_Context $context)
    {
        $form = parent::_viewOrders($context);
        $form['orders']['#header']['content'] = __('Listing', 'sabai-directory');
        return $form;
    }
    
    protected function _getPlanTypes(Sabai_Context $context)
    {
        return $this->getAddon()->getPlanTypes();
    }
}
