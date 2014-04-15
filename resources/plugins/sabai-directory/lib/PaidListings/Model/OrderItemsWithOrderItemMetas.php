<?php
class Sabai_Addon_PaidListings_Model_OrderItemsWithOrderItemMetas extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('orderitemmeta_orderitem_id', 'OrderItemMeta', $collection, 'OrderItemMetas');
    }
}