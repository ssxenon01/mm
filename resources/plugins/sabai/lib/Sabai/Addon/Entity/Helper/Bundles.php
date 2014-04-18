<?php
class Sabai_Addon_Entity_Helper_Bundles extends Sabai_Addon_Entity_Helper_Bundle
{
    public function help(Sabai $application, array $bundleNames)
    {
        $ret = array();
        foreach ($bundleNames as $k => $bundle_name) {
            if (isset($this->_bundles[$bundle_name])) {
                $ret[$bundle_name] = $this->_bundles[$bundle_name];
                unset($bundleNames[$k]);
            }
        }
        if (!empty($bundleNames)) {
            foreach ($application->getModel('Bundle', 'Entity')->name_in($bundleNames)->fetch() as $bundle) {
                $this->_bundles[$bundle->name] = $ret[$bundle->name] = $bundle;
            }
        }
        return $ret;
    }
}