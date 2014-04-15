<?php
class Sabai_Addon_PaidListings_Model_PlansWithOrders extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('order_plan_id', 'Order', $collection, 'Orders');
    }
}