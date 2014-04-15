<?php
if (!defined('SABAI_WORDPRESS_PLUGIN')) {
    define('SABAI_WORDPRESS_PLUGIN', basename(dirname(dirname(__FILE__))));
}
if (!defined('SABAI_WORDPRESS_PATH')) {
    define('SABAI_WORDPRESS_PATH', WP_PLUGIN_DIR . '/' . SABAI_WORDPRESS_PLUGIN);
}
if (defined('SABAI_WORDPRESS_PATH_APPEND') && SABAI_WORDPRESS_PATH_APPEND) {
    set_include_path(get_include_path() . PATH_SEPARATOR . SABAI_WORDPRESS_PATH . '/lib');
} else {
    set_include_path(SABAI_WORDPRESS_PATH . '/lib' . PATH_SEPARATOR . get_include_path());
}

// Define custom session directory if session not yet started
if (defined('SABAI_WORDPRESS_SESSION_PATH') && !session_id()) {
    session_save_path(SABAI_WORDPRESS_SESSION_PATH);
}