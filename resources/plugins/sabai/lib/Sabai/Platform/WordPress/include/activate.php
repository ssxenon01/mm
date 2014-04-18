<?php
function sabai_platform_wordpress_activate(Sabai_Platform_WordPress $platform)
{
    if (!function_exists('mb_detect_encoding')) {
        die('Sabai plugin requires the PHP mbstring extension.');
    }
    
    if (defined('SABAI_WORDPRESS_SESSION_PATH')) {
        if (!is_writeable(SABAI_WORDPRESS_SESSION_PATH)) {
            die(sprintf('Configuration error: The path %s set for SABAI_WORDPRESS_SESSION_PATH is not writeable by the server.', SABAI_WORDPRESS_SESSION_PATH));
        }
    }
    
    if (intval(ini_get('max_execution_time')) < 600){
        @ini_set('max_execution_time', '600');
    }
    if (intval(ini_get('memory_limit')) < 128){
        @ini_set('memory_limit', '128M');
    }
    
    // List of plugins to install and initial configurations
    $addons = array(
        'System' => array(),
        'jQuery' => array(),
        'Form' => array(),
        'Widgets' => array(),
        'WordPress' => array(),
        'Field' => array(),
        'Entity' => array(),
        'HTML' => array(),
        'Markdown' => array(),
        'Voting' => array(),
        'Comment' => array(),
        'Content' => array(),
        'Taxonomy' => array(),
        'Autocomplete' => array(),
        'Date' => array(),
        'FieldUI' => array(),
        'File' => array(),
    );
    $log = new ArrayObject();

    $log[] = 'Clearing old cache data if any...';
    $platform->clearCache();
    $log[] = 'done...';

    if (is_dir($clones_dir = $platform->getWriteableDir() . '/System/clones')
        && ($files = glob($clones_dir . '/*.php'))
    ) {
        foreach($files as $file) {
            @unlink($file);
        }
    }
    
    $log[] = 'Installing Sabai...';
    
    try {
        $sabai = $platform->getSabai(true, true);
        // If no exception, the plugin is already installed so do nothing
        return;
    } catch (Sabai_NotInstalledException $e) {
        $sabai = $platform->getSabai(false); // get Sabai without loading addons
    }
    
    // Pre install
    $platform->updateSabaiOption('page_slugs', array(array(), array(), array()), true);
    $platform->updateSabaiOption('admin_menus', array(), true);
    
    // Install
    if (!sabai_platform_wordpress_activate_addons($sabai, $addons, $log)) {
        
    }
    
    $log[] = 'done.';

    $platform->updateSabaiOption('install_log', implode('', (array)$log));
}

function sabai_platform_wordpress_activate_addons(Sabai $sabai, array $addons, $log)
{    
    try {
        // Install the System addon
        $system = $sabai->fetchAddon('System')->install($log);
        if (!$system_entity = $system->getModel('Addon')->name_is('System')->fetchOne()) {
            $log[] = 'failed fetching the System addon entity.';

            return false;
        }
        // This will be commited later when the SabaiAddonInstalled event is triggered
        $system_entity->setParams($system->getDefaultConfig(), array(), false);
        $system_entity->events = $system->getEvents();
        $system_entity->commit();
    } catch (Exception $e) {
        $log[] = sprintf('failed installing the System addon. Error: %s', $e->getMessage());

        return false;
    }
    
    $sabai->reloadAddons();

    $log[] = 'System installed...';

    // Install other required addons
    $addons_installed = array('System' => $system_entity);
    $install_failed = false;
    foreach ($addons as $addon => $addon_settings) {
        if (isset($addons_installed[$addon])) continue;
        
        $addon_settings = array_merge(array('params' => array(), 'priority' => 1), $addon_settings);
        try {
            $entity = $sabai->InstallAddon($addon, $addon_settings['params'], $addon_settings['priority'], $log);
        } catch (Exception $e) {
            $install_failed = true;
            $log[] = sprintf('failed installing required addon %s. Error: %s', $addon, $e->getMessage());
            break;
        }

        $addons_installed[$addon] = $entity;

        $log[] = sprintf('%s addon installed...', $addon);
    }

    $sabai->reloadAddons();

    $log[] = 'done...';

    if (!$install_failed) {
        foreach ($addons_installed as $addon => $addon_entity) {
            $sabai->doEvent('SabaiAddonInstalled', array($addon_entity, $log));
        }
        // Reload addons data
        $sabai->reloadAddons();
    }

    if ($install_failed) {
        if (!empty($addons_installed)) {
            // Uninstall all addons
            $log[] = 'Uninstalling installed addons...';
            foreach (array_keys($addons_installed) as $addon) {
                try {
                    $sabai->getAddon($addon)->uninstall($log);
                } catch (Exception $e) {
                    $log[] = sprintf('failed uninstalling the %s addon! You must manually uninstall the addon. Error: %s...', $addon, $e->getMessage());
                    continue;
                }
                $log[] = sprintf('%s addon uninstalled...', $addon);
            }
        }
    }

    return !$install_failed;
}