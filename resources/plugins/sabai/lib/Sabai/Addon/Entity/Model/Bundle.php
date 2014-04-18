<?php
class Sabai_Addon_Entity_Model_Bundle extends Sabai_Addon_Entity_Model_Base_Bundle
{    
    public function getPath()
    {
        return $this->path ? $this->path : '/' . str_replace('_', '/', $this->name);
    }
}

class Sabai_Addon_Entity_Model_BundleRepository extends Sabai_Addon_Entity_Model_Base_BundleRepository
{
}