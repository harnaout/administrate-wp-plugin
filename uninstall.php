<?php
use ADM\WPPlugin as ADMWPP;

if (!defined('WP_UNINSTALL_PLUGIN')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

if (!is_user_logged_in()) {
    wp_die('You must be logged in to run this script.');
}

if (!current_user_can('install_plugins')) {
    wp_die('You do not have permission to run this script.');
}

if (WP_UNINSTALL_PLUGIN) {
    define('ADMWPP_DIR', plugin_dir_path(__FILE__));
    define('ADMWPP_VERSION_KEY', 'admwpp_version');

    if (class_exists('ADM\WPPlugin\Main')) {
        // Run uninstall hook
        $instance = ADMWPP\Main::instance();
        $instance->uninstall();
    }
}
