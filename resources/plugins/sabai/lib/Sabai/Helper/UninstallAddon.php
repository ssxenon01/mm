<?php
class Sabai_Helper_UninstallAddon extends Sabai_Helper
{
    public function help(Sabai $application, $addon, ArrayObject $log = null)
    {
        if (!$addon instanceof Sabai_Addon_System_Model_Addon) {
            if (!$addon = $application->getModel('Addon', 'System')->name_is($addon)->fetchOne()) {
                throw new Sabai_RuntimeException(__('Failed fetching addon.', 'sabai'));
            }
        }
        if ($application->getModel('Addon', 'System')->parentAddon_is($addon->name)->count()) {
            throw new Sabai_RuntimeException(sprintf(__('Addon %s may not be uninstalled.', 'sabai'), $addon->name));
        }

        $addon->markRemoved();
        $addon->commit();
        $application->getAddon($addon->name)->uninstall($log);
        
        return $addon;
    }
}

