<?php
class Sabai_Addon_Entity_Model_EntityTypesWithBundles extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('bundle_entitytype_name', 'Bundle', $collection, 'Bundles');
    }
}