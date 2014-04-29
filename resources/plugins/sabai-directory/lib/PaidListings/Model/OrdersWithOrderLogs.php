<?php
class Sabai_Addon_PaidListings_Model_OrdersWithOrderLogs extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('orderlog_order_id', 'OrderLog', $collection, 'OrderLogs');
    }
}