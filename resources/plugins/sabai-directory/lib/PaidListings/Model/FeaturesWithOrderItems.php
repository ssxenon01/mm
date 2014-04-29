<?php
class Sabai_Addon_PaidListings_Model_FeaturesWithOrderItems extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('orderitem_feature_name', 'OrderItem', $collection, 'OrderItems');
    }
}