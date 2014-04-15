<?php
/*
Plugin Name: Business Directory
Plugin URI: http://pantera.mn
Description: Business directory plugin.
Author: gundee @ pantera
Author URI: http://pantera.mn
Text Domain: xenon-directory
Domain Path: /languages
Version: 1
*/
if (is_admin()) {
    include_once dirname(__FILE__) . '/include/activate.php';
} else {
    include_once dirname(__FILE__) . '/include/shortcodes.php';
}