<?php
/*
Plugin Name: SabaiDirectory 
Plugin URI: http://codecanyon.net/user/onokazu/portfolio?ref=Harem3d
Description: Business directory plugin for WordPress.
Author: somi @ ariyan.org
Author URI: http://codecanyon.net/user/onokazu/portfolio?ref=Harem3d
Text Domain: sabai-directory
Domain Path: /languages
Version: 1.2.18
*/
if (is_admin()) {
    include_once dirname(__FILE__) . '/include/activate.php';
} else {
    include_once dirname(__FILE__) . '/include/shortcodes.php';
}