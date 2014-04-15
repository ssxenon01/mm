<?php
if (!defined('ABSPATH')) exit;

require_once dirname(__FILE__) . '/common_path.php';
require_once 'Sabai/Platform/WordPress.php';
$sabai_wordpress = Sabai_Platform_WordPress::getInstance(SABAI_WORDPRESS_PLUGIN);