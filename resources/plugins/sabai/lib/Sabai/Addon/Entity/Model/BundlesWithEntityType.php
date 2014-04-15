<?php
class Sabai_Addon_Entity_Model_BundlesWithEntityType extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('entitytype_name', 'EntityType', $collection);
    }
}