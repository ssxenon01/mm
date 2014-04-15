<?php
class Sabai_Addon_System_Model_PermissionCategoriesWithPermissions extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('permission_permissioncategory_name', 'Permission', $collection, 'Permissions');
    }
}