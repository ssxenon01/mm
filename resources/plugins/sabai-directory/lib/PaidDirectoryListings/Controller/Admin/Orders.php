<?php
require_once dirname(dirname(__FILE__)) . '/Orders.php';

class Sabai_Addon_PaidDirectoryListings_Controller_Admin_Orders extends Sabai_Addon_PaidDirectoryListings_Controller_Orders
{
    protected function _doExecute(Sabai_Context $context)
    {
        parent::_doExecute($context);
        $context->clearTabs();
    }
}