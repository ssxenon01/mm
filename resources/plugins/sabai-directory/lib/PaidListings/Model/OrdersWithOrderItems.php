<?php
class Sabai_Addon_PaidListings_Model_OrdersWithOrderItems extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('orderitem_order_id', 'OrderItem', $collection, 'OrderItems');
    }
}