<?php
interface Sabai_Addon_Content_IPermissions
{
    public function contentGetPermissions(Sabai_Addon_Entity_Model_Bundle $bundle);
    public function contentGetDefaultPermissions(Sabai_Addon_Entity_Model_Bundle $bundle);
}