<?php
class Sabai_Addon_PaidListings_Model_OrderItemMetasWithOrderItem extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('orderitem_id', 'OrderItem', $collection);
    }
}