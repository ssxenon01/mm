<?php
if (!defined('ABSPATH')) exit;

function sabai_wordpress_directory_activate()
{
    require_once WP_PLUGIN_DIR . '/sabai/include/functions.php';
    sabai_wordpress_activate_plugin('SabaiDirectory', 'Directory');
}
register_activation_hook(dirname(dirname(__FILE__)) . '/sabai-directory.php', 'sabai_wordpress_directory_activate');