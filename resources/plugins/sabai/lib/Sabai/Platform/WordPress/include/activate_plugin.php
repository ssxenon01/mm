<?php
function sabai_platform_wordpress_activate_plugin(Sabai_Platform_WordPress $platform, $pluginName, $primaryAddonName)
{
    try {
        $sabai = $platform->getSabai(true, true);
    } catch (Sabai_NotInstalledException $e) {
        // Sabai is not installed
        die(__('Sabai plugin must be installed.', 'sabai'));
    }
    if (!$sabai->isAddonLoaded($primaryAddonName)) {
        die(sprintf(
            __('This plugin does not need to be activated. To install %1$s, go to the <a target="_parent" href="%2$s">installable add-ons listing section</a> and install the %3$s add-on from there.', 'sabai'),
            htmlspecialchars($pluginName, ENT_QUOTES),
            admin_url('admin.php?page=sabai/settings#sabai-system-admin-addons-installable'),
            $primaryAddonName
        ));
    } else {
        $local_version = $sabai->getAddon($primaryAddonName)->getVersion();
        $installed_addon = $sabai->getInstalledAddon($primaryAddonName, true);
        $installed_version = $installed_addon['version'];
        if (version_compare($installed_version, $local_version, '<')) {
            die(sprintf(
                __('This plugin does not need to be activated. To update %1$s, go to the <a target="_parent" href="%2$s">add-ons listing section</a> and upgrade the %3$s add-on from there.', 'sabai'),
                htmlspecialchars($pluginName, ENT_QUOTES),
                admin_url('admin.php?page=sabai/settings#sabai-system-admin-addons-installed'),
                $primaryAddonName
            ));
        }
        die(sprintf(
            __('This plugin does not need to be activated. %s is already installed and running.', 'sabai'),
            htmlspecialchars($pluginName, ENT_QUOTES)
        ));
    }
}