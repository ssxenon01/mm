<?php
require_once dirname(__FILE__) . '/Orders.php';

class Sabai_Addon_PaidDirectoryListings_Controller_MyOrders extends Sabai_Addon_PaidDirectoryListings_Controller_Orders
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = parent::_doGetFormSettings($context, $formStorage);
        unset($form['orders']['#header']['user']);
        return $form;
    }

    protected function _getCriteria(Sabai_Context $context)
    {
        return parent::_getCriteria($context)->userId_is($this->getUser()->id);
    }
    
    protected function _isValidOrder(Sabai_Context $context, Sabai_Addon_PaidListings_Model_Order $order)
    {
        return $order->user_id === $this->getUser()->id;
    }
}
