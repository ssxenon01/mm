<?php
/*
Plugin Name: Sabai
Description: Sabai is a web application framework for WordPress.
Author: onokazu
Author URI: http://codecanyon.net/user/onokazu/portfolio?ref=onokazu
Text Domain: sabai
Domain Path: /languages
Version: 1.2.32
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

function is_sabai()
{
    return is_page()
        && isset($GLOBALS['post'])
        && ($slugs = get_option('sabai_sabai_page_slugs', false))
        && is_array($slugs[2])
        && array_search($GLOBALS['post']->ID, $slugs[2]);
}
