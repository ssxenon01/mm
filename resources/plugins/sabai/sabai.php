<?php
/*
Plugin Name: Sabai
Description: Sabai is a web application framework for WordPress.
Author: onokazu
Author URI: http://codecanyon.net/user/onokazu/portfolio?ref=onokazu
Text Domain: sabai
Domain Path: /languages
Version: 1.2.30
*/

function sabai_wordpress_run()
{
    require dirname(__FILE__) . '/include/common.php';
    $sabai_wordpress->run();
}
add_action('plugins_loaded', 'sabai_wordpress_run');

if (is_admin()) {
    function sabai_wordpress_activation_hook()
    {
        require dirname(__FILE__) . '/include/common.php';
        $sabai_wordpress->activate();
    }
    register_activation_hook(__FILE__, 'sabai_wordpress_activation_hook');
    
    function sabai_wordpress_uninstall_hook()
    {
        require dirname(__FILE__) . '/include/common.php';
        try {
            $sabai_wordpress->getSabai(true, true)->Uninstall();
        } catch (Sabai_NotInstalledException $e) {
            return;
        }
    }
    register_uninstall_hook(__FILE__, 'sabai_wordpress_uninstall_hook');
}
