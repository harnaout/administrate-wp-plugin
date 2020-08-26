<?php
/*
  Plugin Name: Administrate
  Description: This is a plugin for Administrate
  Version: 1.0.0
  Author: Administrate
  Author URI: http://getadministrate.com/
 */

/*
  Copyright 2020 by Administrate

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// --------------------------------------------------------------------
// Plugin prefix
// --------------------------------------------------------------------
// 'admwpp_' prefix is derived from [Adm]inistrate [wp] [p]lugin
// choose your own prefix and do a search & replace on the whole project
// --------------------------------------------------------------------

// --------------------------------------------------------------------
// Define some useful constants
// --------------------------------------------------------------------

define('ADMWPP_VERSION', '3.4.3');
define('ADMWPP_DIR', plugin_dir_path(__FILE__));
define('ADMWPP_URL', plugin_dir_url(__FILE__));
define('ADMWPP_SRC', 'src/');
define('ADMWPP_VENDOR', 'vendor/');
define('ADMWPP_URL_ROUTES', plugin_dir_url(__FILE__) . ADMWPP_SRC . 'routes.php');
define('ADMWPP_PLUGIN_NAME', plugin_basename(__FILE__));
define('ADMWPP_ASSETS_URL', plugin_dir_url(__FILE__) . "assets/");

define('ADMWPP_DATE_FORMAT', 'Y-m-d H:i:s');
define('ADMWPP_TEXT_DOMAIN', 'admwpp');

define('ADMWPP_WEBSITE', 'http://getadministrate.com/');

define('ADMWPP_TEMPLATES_DIR', plugin_dir_path(__FILE__) . 'templates/');
define('ADMWPP_ADMIN_TEMPLATES_DIR', plugin_dir_path(__FILE__) . 'templates/admin-views/');

// Sported plugins
define('ADMWPP_WPML_PATH', 'sitepress-multilingual-cms/sitepress.php');
define('ADMWPP_WPSEO_PATH', 'wordpress-seo/wp-seo.php');
define('ADMWPP_WPSEO_PREMIUM_PATH', 'wordpress-seo-premium/wp-seo-premium.php');

define('ADMWPP_DEFAULT_LANG', 'en');

// Define the environment
if (!defined('ADMWPP_ENV')) {
    define('ADMWPP_ENV', 'production');
}

// To load the non minified versions of the CSS and JS files
// in order to debug during development, set this to true.
if (!defined('ADMWPP_DEVELOPMENT')) {
    define('ADMWPP_DEVELOPMENT', false);
}

// Load globals, helpers, Autoloader
require_once(ADMWPP_DIR . ADMWPP_SRC . 'globals.php');
require_once(ADMWPP_DIR . ADMWPP_SRC . 'helpers.php');
require_once(ADMWPP_DIR . ADMWPP_SRC . 'Autoloader.php');

// PHP SDK Autoloader
require_once(ADMWPP_DIR . ADMWPP_VENDOR . 'autoload.php');

use ADM\WPPlugin as ADMWPP;

// Instantiate the loader
$loader = new ADMWPP\Autoloader;

// Register the autoloader
$loader->register();

// Register the base directories for the namespace prefix
$loader->addNamespace('ADM\WPPlugin', plugin_dir_path(__FILE__) . ADMWPP_SRC);

if (class_exists('ADM\WPPlugin\Main')) {
  // --------------------------------------------------------------------
  // Activation, deactivation and uninstall hooks
  // --------------------------------------------------------------------
    register_activation_hook(__FILE__, array('ADM\WPPlugin\Main', 'activate'));
    register_deactivation_hook(__FILE__, array('ADM\WPPlugin\Main', 'deactivate'));

    $instance = ADMWPP\Main::instance();

    if (ADMWPP\Main::active()) {
        $instance->loadActiveFeatures();
    }
}

// --------------------------------------------------------------------
// Define plugin version
// --------------------------------------------------------------------
if (!defined('ADMWPP_VERSION_KEY')) {
    define('ADMWPP_VERSION_KEY', 'admwpp_version');
}
add_option(ADMWPP_VERSION_KEY, ADMWPP_VERSION);

// --------------------------------------------------------------------
// Perform any necessary updates (to db or whatever)
// when version changes
// --------------------------------------------------------------------
$current_version = get_option(ADMWPP_VERSION_KEY);
if ($current_version != ADMWPP_VERSION) {
  // Execute your upgrade logic here
  // ...

  // Then update the version value
    update_option(ADMWPP_VERSION_KEY, ADMWPP_VERSION);
}
