<?php
class Sabai_Addon_Entity_Model_EntityTypesWithFieldConfigs extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('fieldconfig_entitytype_name', 'FieldConfig', $collection, 'FieldConfigs');
    }
}