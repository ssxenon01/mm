<?php
class Sabai_Addon_PaidListings_Model_OrdersWithUser extends Sabai_ModelEntityWithUser
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
    }
}