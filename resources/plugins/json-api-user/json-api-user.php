<?php

/*

  Plugin Name: JSON API User

  Plugin URI: http://downloads.wordpress.org/plugin/json-api-user.zip

  Description: Extends the JSON API for RESTful user registration, password reset, Facebook Login, user meta and BuddyPress Profile related functions

  Version: 1.1

  Author: Ali Qureshi

  Author URI: http://www.parorrey.com/

  License: GPLv3

 */



include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

define('JSON_API_USER_HOME', dirname(__FILE__));



if (!is_plugin_active('json-api/json-api.php')) {

    add_action('admin_notices', 'pim_draw_notice_json_api');

    return;

}



add_filter('json_api_controllers', 'pimJsonApiController');

add_filter('json_api_user_controller_path', 'setUserControllerPath');

load_plugin_textdomain('json-api-user', false, basename(dirname(__FILE__)) . '/languages');



function pim_draw_notice_json_api() {

    echo '<div id="message" class="error fade"><p style="line-height: 150%">';

    _e('<strong>JSON API User</strong></a> requires the JSON API plugin to be activated. Please <a href="wordpress.org/plugins/json-api/‎">install / activate JSON API</a> first.', 'json-api-user');

    echo '</p></div>';

}



function pimJsonApiController($aControllers) {

    $aControllers[] = 'User';

    return $aControllers;

}



function setUserControllerPath($sDefaultPath) {

    return dirname(__FILE__) . '/controllers/User.php';

}