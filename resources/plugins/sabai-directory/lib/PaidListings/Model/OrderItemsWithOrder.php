<?php
class Sabai_Addon_PaidListings_Model_OrderItemsWithOrder extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('order_id', 'Order', $collection);
    }
}