<?php
class Sabai_Addon_PaidListings_Model_OrdersWithPlan extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('plan_id', 'Plan', $collection);
    }
}