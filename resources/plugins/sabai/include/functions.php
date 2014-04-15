<?php
function sabai_wordpress_activate_plugin($pluginName, $primaryAddonName)
{
    if (!is_plugin_active('sabai/sabai.php')
        || !file_exists(WP_PLUGIN_DIR . '/sabai/include/common.php')
    ) {
        die(__('Sabai plugin must be installed and active.', 'sabai'));
    } 
    require WP_PLUGIN_DIR . '/sabai/include/common.php';
    $sabai_wordpress->activatePlugin($pluginName, $primaryAddonName);
}