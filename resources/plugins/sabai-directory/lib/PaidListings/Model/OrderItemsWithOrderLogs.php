<?php
class Sabai_Addon_PaidListings_Model_OrderItemsWithOrderLogs extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('orderlog_orderitem_id', 'OrderLog', $collection, 'OrderLogs');
    }
}