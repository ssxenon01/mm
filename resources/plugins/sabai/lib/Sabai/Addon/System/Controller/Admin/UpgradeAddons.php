<?php
class Sabai_Addon_System_Controller_Admin_UpgradeAddons extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        // Must be an Ajax request
        if (!$context->getRequest()->isAjax()) {
            $context->setBadRequestError();
            return;
        }

        // Check request token
        if (!$this->_checkToken($context, 'system_admin_addons')) {
            return;
        }
        
        $addon_names = $context->getRequest()->asArray('addons');
        if (!empty($addon_names)) {
            $addons = $addon_current_versions = array();
            $log = new ArrayObject();
            foreach ($addon_names as $addon_name) {
                // Fetch addon info from the database
                if (!$addon = $this->getAddon('System')->getModel('Addon')->name_is($addon_name)->fetchOne()) {
                    continue;
                }
                $addon_current_versions[$addon_name] = $addon->version;
                $this->doEvent('SabaiAddonUpgrade', array($addon, $log));
                $addons[$addon_name] = $this->UpgradeAddon($addon, array(), $log);
                $log[] = sprintf(__('Add-on %s has been upgraded.', 'sabai'), $addon_name);
            }
            $this->reloadAddons();
            foreach ($addons as $addon_name => $addon) {
                $this->doEvent('SabaiAddonUpgraded', array($addon, $addon_current_versions[$addon_name], $log));
            }
            $this->getPlatform()->clearCache();
            foreach ($log as $_log) {
                $context->addFlash($_log);
            }
        }
        
        // Send success response
        $context->setSuccess('/settings');
    }
}