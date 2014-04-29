<?php
/*
Plugin Name: SabaiDirectory
Plugin URI: http://codecanyon.net/user/onokazu/portfolio
Description: Business directory plugin for WordPress.
Author: onokazu
Author URI: http://codecanyon.net/user/onokazu/portfolio?ref=onokazu
Text Domain: sabai-directory
Domain Path: /languages
Version: 1.2.30
*/
if (is_admin()) {
    include_once dirname(__FILE__) . '/include/activate.php';
} else {
    include_once dirname(__FILE__) . '/include/shortcodes.php';
}
