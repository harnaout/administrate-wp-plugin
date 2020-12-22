<?php
namespace ADM\WPPlugin;

use ADM\WPPlugin\Api;
use ADM\WPPlugin\PostTypes;

/**
 * Construct the main plugin class "Main"
 * */
if (!class_exists('Main')) {

    /**
     * This class will be responsible for setting up the plugin
     * and anything that goes with it
     *
     * @package default
     *
     */
    class Main
    {

        protected static $instance;
        protected $settings;
        protected $activation;
        protected $flash;

        //PostTypes
        protected $course;
        //Search
        protected $search;

        /**
         * Initializes plugin variables and sets up WordPress hooks/actions
         *
         * @return void
         *
         */
        protected function __construct()
        {
            // Initialize the Plugin Settings Page
            $this->settings = Settings::instance();

            // Initialize the Plugin Flash Messages Class
            $this->flash = FlashMessage::instance();
        }

        /**
         * Static Singleton Factory Method
         * Return an instance of the current class if it exists
         * Construct a new one otherwise
         *
         * @return Main object
         *
         */
        public static function instance()
        {
            if (!isset(self::$instance)) {
                $className = __CLASS__;
                self::$instance = new $className;
            }
            return self::$instance;
        }

        /**
         * Make sure all the necessary classes and files are loaded
         * and initialized if the plugin is active.
         *
         * @return void
         *
         */
        public function loadActiveFeatures()
        {
            if (self::active()) {
                // Set default options values
                self::setDefaults();

                // Add all actions
                $this->addActions();

                // Add Custom Post Types
                $this->course = PostTypes\Course::instance();

                // Add Search
                $this->search = Api\Search::instance();

                // Add all filters
                $this->addFilters();
            }
        }

        public function getFlash()
        {
            return $this->flash;
        }

        /**
         * Checks if the plugin has been activated with Bookwitty
         *
         * @return bool
         *
         */
        public static function active()
        {
            $active = (int) get_option('admwpp_active');
            if ($active == 1) {
                return true;
            }

            update_option('admwpp_active', 0);
            return false;
        }

        /**
         * Sets Default values on activation.
         *
         * @return void
         *
         * */
        public static function setDefaults()
        {
        }


        /**
         * Add main filters
         *
         * @return void
         *
         * */
        protected function addFilters()
        {
        }

        /**
         * Add main actions
         *
         * @return void
         *
         * */
        protected function addActions()
        {
            add_action('init', array($this,'initSession'));

            add_action('admin_init', array($this, 'adminInit'));

            add_action('wp_enqueue_scripts', array($this, 'frontScripts'));

            add_action('admin_enqueue_scripts', array($this, 'adminScripts'));

            add_action('widgets_init', array($this, 'registerWidgets'));

            add_action('wp_footer', array($this,'messageBox'));

            // Add meta boxex for custom posts details
            add_action('add_meta_boxes', array($this, 'addMetaBoxes'));

            add_action('save_post', array($this, 'savePost'));
        }

        public function restApiInit()
        {
            // Activation Callback route
            register_rest_route(
                'admwpp',
                'oauth/callback',
                array(
                    'methods' => 'GET',
                    'callback' => array('ADM\WPPlugin\Controllers\ActivationController', 'callback'),
                    'permission_callback' => '__return_true',
                )
            );

            // Webhooks Callback route
            register_rest_route(
                'admwpp',
                'webhook/callback',
                array(
                    'methods' => 'POST',
                    'callback' => array('ADM\WPPlugin\Controllers\WebhookController', 'callback'),
                    'permission_callback' => '__return_true',
                )
            );
        }

        /**
         * Adds a box to the main column on the Post and Page edit screens.
         */
        public function addMetaBoxes($post_type)
        {
        }

        /**
         * CALLBACK FUNCTION FOR:
         * add_action('save_post', array($this, 'save_post'));
         * Save the metaboxes for the Custom Post Type
         *
         * @return void
         *
         */
        public static function savePost($post_id)
        {
            // verify if this is an auto save routine.
            // If it is our form has not been submitted, so we don't want to do anything
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }
        }

        /**
         * Start Session
         *
         * @return void
         *
         * */
        public static function initSession()
        {
            if (!session_id()) {
                session_start();
            }
        }

        public static function messageBox()
        {
            $content;
            ob_start();
            include(ADMWPP_TEMPLATES_DIR . 'flash-message.php');
            $content = ob_get_contents();
            ob_end_clean();

            ob_start();
            include(ADMWPP_TEMPLATES_DIR . 'confirm-dialog.php');
            $content .= ob_get_contents();
            ob_end_clean();

            echo $content;
        }

        /**
         * Register the widget
         *
         * @return void
         *
         * */
        public static function registerWidgets()
        {
        }

        /**
         * CALLBACK FUCTION FOR:
         * add_action('admin_init', array($this, 'admin_init'));
         *
         * @return void
         *
         */
        public function adminInit()
        {
            // Require minimum WP version
            add_action('admin_init', array($this, 'require_wordpress_version'));
        }

        /**
         * CALLBACK FUNCTION FOR:
         * add_action('admin_init', array($this, 'require_wordpress_version'));
         *
         * @return void
         *
         */
        public static function require_wordpress_version()
        {
            global $wp_version;
            $plugin_data = get_plugin_data(ADMWPP_DIR . 'administrate-wp-plugin.php', false);
            $min_wp_version = "3.5";

            if (version_compare($wp_version, $min_wp_version, "<")) {
                if (is_plugin_active(ADMWPP_PLUGIN_NAME)) {
                    deactivate_plugins(ADMWPP_PLUGIN_NAME);
                    wp_die(
                        "'" .
                        $plugin_data['Name'] .
                        "' requires WordPress " .
                        $min_wp_version .
                        " or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='" .
                        admin_url() .
                        "'>WordPress admin</a>."
                    );
                }
            }
        }

        /**
         * Function to load jQuery UI
         *
         * @return void
         */
        public static function addJqueryUi()
        {
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-widget');
            wp_enqueue_script('jquery-ui-mouse');
            wp_enqueue_script('jquery-ui-accordion');
            wp_enqueue_script('jquery-ui-autocomplete');
            wp_enqueue_script('jquery-ui-slider');
            wp_enqueue_script('jquery-ui-tabs');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-draggable');
            wp_enqueue_script('jquery-ui-droppable');
            wp_enqueue_script('jquery-ui-selectable');
            wp_enqueue_script('jquery-ui-position');
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('jquery-ui-tooltip');
            wp_enqueue_script('jquery-ui-resizable');
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script('jquery-ui-button');
        }

        /**
         * Function to load jQuery UI Effects
         *
         * @return void
         */
        public static function addJqueryUiEffects()
        {
            wp_enqueue_script('jquery-effects-core');
            wp_enqueue_script('jquery-effects-blind');
            wp_enqueue_script('jquery-effects-bounce');
            wp_enqueue_script('jquery-effects-clip');
            wp_enqueue_script('jquery-effects-drop');
            wp_enqueue_script('jquery-effects-explode');
            wp_enqueue_script('jquery-effects-fade');
            wp_enqueue_script('jquery-effects-fold');
            wp_enqueue_script('jquery-effects-highlight');
            wp_enqueue_script('jquery-effects-pulsate');
            wp_enqueue_script('jquery-effects-scale');
            wp_enqueue_script('jquery-effects-shake');
            wp_enqueue_script('jquery-effects-slide');
            wp_enqueue_script('jquery-effects-transfer');
        }

        /**
         * CALLBACK FUNCTION FOR:
         * add_action('wp_enqueue_scripts', array($this, 'frontScripts'));
         *
         * @return void
         *
         */
        public static function frontScripts()
        {
            $stylesSettings = (int) Settings::instance()->getSettingsOption('general', 'styles');
            // Check environment
            if (ADMWPP_DEVELOPMENT) {
                $admwpp_css = ADMWPP_URL . 'assets/css/admwpp.css';
                $admwpp_js  = ADMWPP_URL . 'assets/js/admwpp-debug.js';
            } else {
                $admwpp_css = ADMWPP_URL . 'assets/css/admwpp.min.css';
                $admwpp_js  = ADMWPP_URL . 'assets/js/admwpp.min.js';
            }

            // ------------------------------------------------------
            // Register the css
            // ------------------------------------------------------
            wp_register_style(
                'administrate',
                $admwpp_css,
                '',
                ADMWPP_VERSION
            );

            wp_register_style(
                'admwpp-font-awesome',
                '//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.css',
                array(),
                ADMWPP_VERSION
            );

            wp_register_style(
                'admwpp-selectric',
                ADMWPP_URL . 'assets/css/plugins/selectric.min.css',
                array(),
                '1.13.0'
            );

            $stylesArray = array(
                'thickbox',
                'wp-jquery-ui-dialog',
            );
            
            if (!$stylesSettings) :
                $stylesArray[] = 'admwpp-font-awesome';
                $stylesArray[] = 'admwpp-selectric';
                $stylesArray[] = 'administrate';
            endif;
            
            wp_enqueue_style($stylesArray);

            // ------------------------------------------------------
            // Register the js
            // ------------------------------------------------------
            wp_register_script(
                'administrate',
                $admwpp_js,
                '',
                ADMWPP_VERSION,
                true
            );

            wp_register_script(
                'admwpp-selectric',
                ADMWPP_URL . 'assets/js/plugins/selectric.min.js',
                '',
                '1.13.0',
                true
            );

            $scriptArray = array(
                'jquery',
                'jquery-ui-core',
                'jquery-ui-dialog',
                'jquery-effects-core',
                'jquery-ui-datepicker',
                'administrate',
            );

            if (!$stylesSettings) :
                $scriptArray[] = 'admwpp-selectric';
            endif;
            
            wp_enqueue_script($scriptArray);

            $admwppLocalize = array(
                'language' => admwppPrimaryLanguage(),
                'locale' => get_locale(),
                'baseUrl' => ADMWPP_URL,
                'routeUrl' => ADMWPP_URL_ROUTES,
                'search' => array(
                    'dateFormat' => ADMWPP_SEARCH_DATE_FORMAT,
                    'perPage' => ADMWPP_SEARCH_PER_PAGE
                )
            );

            wp_localize_script('administrate', 'admwpp', $admwppLocalize);

        }

        /**
         * CALLBACK FUNCTION FOR:
         * add_action('admin_enqueue_scripts', array($this, 'adminScripts'));
         *
         * @return void
         *
         */
        public static function adminScripts()
        {
            // Check environment
            if (ADMWPP_DEVELOPMENT) {
                $admwpp_css = ADMWPP_URL . 'assets/css/admin.css';
                $admwpp_js  = ADMWPP_URL . 'assets/js/admin-debug.js';
            } else {
                $admwpp_css = ADMWPP_URL . 'assets/css/admin.min.css';
                $admwpp_js  = ADMWPP_URL . 'assets/js/admin.min.js';
            }

            // ------------------------------------------------------
            // Register the css
            // ------------------------------------------------------
            wp_register_style(
                'administrate',
                $admwpp_css,
                '',
                ADMWPP_VERSION
            );

            wp_enqueue_style(
                'font-awesome',
                '//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.css',
                array(),
                ADMWPP_VERSION
            );

            wp_enqueue_style(
                array(
                'wp-color-picker',
                'thickbox',
                'wp-jquery-ui-dialog',
                'administrate',
                )
            );

            // ------------------------------------------------------
            // Register the js
            // ------------------------------------------------------
            wp_register_script(
                'administrate-admin',
                $admwpp_js,
                '',
                ADMWPP_VERSION,
                true
            );

            wp_register_script(
                'clippy',
                ADMWPP_URL . 'assets/js/plugins/clippy/jquery.clippy.min.js',
                '',
                ADMWPP_VERSION,
                true
            );

            wp_register_script(
                'rails',
                ADMWPP_URL . 'assets/js/plugins/rails.js',
                '',
                ADMWPP_VERSION,
                true
            );

            wp_enqueue_script(
                array(
                    'jquery',
                    'wp-color-picker',
                    'clippy',
                    'rails',
                    'administrate-admin'
                )
            );

            self::addJqueryUi();
            self::addJqueryUiEffects();

            wp_localize_script('administrate-admin', 'admwpp_base_url', ADMWPP_URL);
            wp_localize_script('administrate-admin', 'admwpp_route_url', ADMWPP_URL_ROUTES);

            wp_localize_script('administrate-admin', 'admwpp_language', admwppPrimaryLanguage());
            wp_localize_script('administrate-admin', 'admwpp_locale', get_locale());
        }

        /**
         * Delete Plugin Options
         *
         * @return void
         *
         */
        public static function deleteOptions()
        {
            // Delete Plugin version
            delete_option(ADMWPP_VERSION_KEY);

            // Delete Activation Flag Params.
            delete_option('admwpp_active');
        }

        /**
         * Delete Plugin Taxonomies
         *
         * @return void
         *
         */
        public static function deleteTaxonomies()
        {
        }

        /**
         * CALLBACK FUNCTION FOR:
         * register_activation_hook(__FILE__, array('ADMPlugin', 'activate'));
         * Activates Plugin
         *
         * @return void
         *
         */
        public static function activate()
        {
            // Adds rewrite rules flag
            add_option('admwpp_flush_rewrite_rules', 'true');
        }

        /**
         * CALLBACK FUNCTION FOR:
         * register_deactivation_hook(__FILE__, array('ADMPlugin', 'deactivate'));
         * Deactivates Plugin
         *
         * @return void
         * @author
         * */
        public static function deactivate()
        {
            // Delete rewrite rules flag
            delete_option('admwpp_flush_rewrite_rules');
        }

        /**
         * CALLBACK FUNCTION FOR:
         * register_uninstall_hook(__FILE__, array($this, 'uninstall'));
         * Uninstalls Plugin
         *
         * @return void
         *
         */
        public static function uninstall()
        {
            $uninstall_settings  = get_option('admwpp_uninstall_settings');

            $remove_everything = 0;
            if (isset($uninstall_settings['remove_everything'])) {
                $remove_everything = 1;
            }

            if ($remove_everything) {
                // Delete All Custom Taxonomies.
                self::deleteTaxonomies();

                // Delete Settings Params.
                delete_option('admwpp_account_settings');
                delete_option('admwpp_general_settings');
                delete_option('admwpp_uninstall_settings');
            }
            self::deleteOptions();
        }
    }
    // END Main
}
