<?php
class Sabai_Addon_System_Model_PermissionsWithPermissionCategory extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('permissioncategory_name', 'PermissionCategory', $collection);
    }
}