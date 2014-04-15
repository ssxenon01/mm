<?php
class Sabai_Helper_UpgradeAddon extends Sabai_Helper
{
    public function help(Sabai $application, $addon, array $config = array(), ArrayObject $log = null, $force = false)
    {
        if (!isset($log)) {
            $log = new ArrayObject();
        }
        if (!$addon instanceof Sabai_Addon_System_Model_Addon) {
            if (!$addon = $application->getModel('Addon', 'System')->name_is($addon)->fetchOne()) {
                throw new Sabai_RuntimeException(__('Failed fetching addon.', 'sabai'));
            }
        }
        $_addon = $application->getAddon($addon->name);
        $new_version = $_addon->getVersion();
        $current_version = $addon->version;
        
        if (!$force
            && !$_addon->isUpgradeable($current_version, $new_version)
        ) {
            throw new Sabai_RuntimeException(__('The plugin is not upgradeable.', 'sabai'));
        }

        $_addon->upgrade($current_version, $log);
        $addon->version = $new_version;
        $addon->events = $_addon->getEvents();
        $config += $addon->getParams();
        $config += $_addon->getDefaultConfig();
        $addon->setParams($config, array(), false);
        $addon->commit();
        
        return $addon;
    }
}