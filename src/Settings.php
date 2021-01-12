<?php
namespace ADM\WPPlugin;

use ADM\WPPlugin\Main as Main;

/**
 * Construct the settings class for the plugin
 * */
if (!class_exists('Settings')) {

    /**
     * This class will be responsible for managing the plugin settings
     *
     * @package default
     *
     * */
    class Settings
    {
        protected static $instance;
        protected $settings;
        protected $capability;

        /*
         * For easier overriding we declared the keys
         * here as well as our tabs array which is populated
         * when registering settings
         */
        private $account_settings_key   = 'admwpp_account_settings';
        private $general_settings_key   = 'admwpp_general_settings';
        private $advanced_settings_key  = 'admwpp_advanced_settings';
        private $search_settings_key    = 'admwpp_search_settings';
        private $uninstall_settings_key = 'admwpp_uninstall_settings';

        private $plugin_options_key     = 'admwpp-settings';

        private $plugin_settings_tabs   = array();

        function __construct()
        {
            // Add all actions and filters
            $this->addFilters();
            $this->addActions();

            $this->capability  = 'manage_options';
            $advanced_settings = get_option('admwpp_advanced_settings');
            if ($advanced_settings && isset($advanced_settings['permission'])) {
                $this->capability = 'edit_pages';
            }
        }

        /**
         * Static Singleton Factory Method
         * Return an instance of the current class if it exists
         * Construct a new one otherwise
         *
         * @return Settings object
         * */
        public static function instance()
        {
            if (!isset(self::$instance)) {
                $className = __CLASS__;
                self::$instance = new $className;
            }
            self::$instance->loadSettings();
            return self::$instance;
        }

        /**
         * Add settings filters
         *
         * @return void
         *
         * */
        protected function addFilters()
        {
            add_filter(
                "plugin_action_links_" . ADMWPP_PLUGIN_NAME,
                array($this, 'plugin_action_links'),
                10,
                2
            );

            // Settings Page Capabilities
            add_filter(
                "option_page_capability_admwpp_account_settings",
                array($this, 'option_page_capability'),
                10,
                1
            );
            add_filter(
                "option_page_capability_admwpp_general_settings",
                array($this, 'option_page_capability'),
                10,
                1
            );
            add_filter(
                "option_page_capability_admwpp_layout_settings",
                array($this, 'option_page_capability'),
                10,
                1
            );
            add_filter(
                "option_page_capability_admwpp_search_settings",
                array($this, 'option_page_capability'),
                10,
                1
            );
        }

        /**
         * Add settings actions
         *
         * @return void
         *
         * */
        protected function addActions()
        {
            add_action('init', array($this, 'loadSettings'));

            add_action('admin_init', array($this, 'adminInit'));

            add_action('admin_menu', array($this, 'adminMenu'));

            add_action('admin_notices', array($this, 'adminWarnings'));

            add_action('admin_enqueue_scripts', array('ADM\WPPlugin\Main', 'adminScripts'));
        }

        /*
         * Loads settings from the database into their respective arrays.
         * Uses array_merge to merge with default values if they're
         * missing.
         */
        public function loadSettings()
        {
            $this->settings = array(
                'account'   => (array) get_option($this->account_settings_key),
                'general'   => (array) get_option($this->general_settings_key),
                'advanced'   => (array) get_option($this->advanced_settings_key),
                'search'   => (array) get_option($this->search_settings_key),
                'uninstall' => (array) get_option($this->uninstall_settings_key),
            );
        }

        /*
         * Get settings from instance.
         */
        public function get_settings($settings_key)
        {
            if (isset($this->settings[$settings_key])) {
                return $this->settings[$settings_key];
            }
            return false;
        }

        /*
         * Get option from instance.
         */
        public function getSettingsOption($settings_key, $option)
        {
            if (isset($this->settings[$settings_key][$option])) {
                $value = $this->settings[$settings_key][$option];

                if (admwppIsJson($value)) {
                    $value_array = json_decode($value, true);
                    if (json_last_error() === 0) {
                        return $value_array;
                    }
                }
                return $value;
            }
            return false;
        }

        /*
         * Set option from instance.
         */
        public function setSettingsOption($settings_key, $option, $value)
        {
            if (isset($this->settings[$settings_key])) {
                $this->settings[$settings_key][$option] = $value;
            }
            return update_option("admwpp_" . $settings_key . "_settings", $this->settings[$settings_key]);
        }

        /**
         * CALLBACK FUCTION FOR:
         * add_action('admin_init', array($this, 'admin_init'));
         *
         * @return void
         *
         * */
        public function adminInit()
        {
            // Init plugin options to white list our options
            $this->init_settings();
        }

        /**
         * Initialize Plugin Options/Settings
         *
         * @return void
         *
         * */
        protected function init_settings()
        {
            Settings::createAccountTab();

            if (Main::active()) {
                Settings::createGeneralTab();
                Settings::createAdvancedTab();
                Settings::createSearchTab();
                Settings::createUninstallTab();

                $this->createSubmenus();

                // Check if on Settings page and we are updating
                // the settings then flush the rewrite rules.
                if (isset($_GET['page']) && "admwpp-settings" == $_GET['page']) {
                    if (isset($_GET['settings-updated']) && "true" == $_GET['settings-updated']) {
                        flush_rewrite_rules(false);
                    }
                }
            }
        }

        // --------------------------------------------------------------------
        // General Tab Creation
        // --------------------------------------------------------------------
        protected function createAccountTab()
        {
            $settings_key = $this->account_settings_key;
            $this->plugin_settings_tabs[$settings_key] = 'My Account';
            register_setting($settings_key, $settings_key);

            // Setting Default options values.
            $settings = get_option($settings_key);

            $settings_index = 'account';

            $this->settings[$settings_index] = $settings;
            update_option($settings_key, $settings);

            Settings::createAccountSection($settings_index);
            Settings::seperatorSection($settings_index, 'account');
            Settings::createAccountWeblinkSection($settings_index);
        }

        // --------------------------------------------------------------------
        // General Tab Creation
        // --------------------------------------------------------------------
        protected function createGeneralTab()
        {
            global $ADMWPP_LANG;
            reset($ADMWPP_LANG);

            $settings_key = $this->general_settings_key;
            $this->plugin_settings_tabs[$settings_key] = 'General';
            register_setting($settings_key, $settings_key);

            // Setting Default options values.
            $settings = get_option($settings_key);
            $settings_index = 'general';

            if (!isset($settings) && empty($settings)) {
                $settings['styles'] = 0;
                // $locale = get_locale();

                // if (file_exists(ADMWPP_DIR . '/languages/' . $locale . '.mo')) {
                //     $settings['language'] = $locale;
                // } else {
                //     $settings['language'] = key($ADMWPP_LANG);
                // }
            }

            $this->settings[$settings_index] = $settings;
            update_option($settings_key, $settings);

            //Settings::createLanguageSection($settings_index);
            Settings::createStylingSection($settings_index);
            Settings::seperatorSection($settings_index, 'language');
        }

        // --------------------------------------------------------------------
        // Advanced Tab Creation
        // --------------------------------------------------------------------
        protected function createAdvancedTab()
        {
            $settings_key = $this->advanced_settings_key;
            $this->plugin_settings_tabs[$settings_key] = 'Advanced';
            register_setting($settings_key, $settings_key);

            // Setting Default options values.
            $settings = get_option($settings_key);

            $settings_index = 'advanced';

            if (empty($settings)) {
                //Setup defaults
            }

            $this->settings[$settings_index] = $settings;
            update_option($settings_key, $settings);

            Settings::createAdvancedSynchCoursesSection($settings_index);
            Settings::seperatorSection($settings_index, 'courses');
            Settings::createAdvancedSynchCategoriesSection($settings_index);
            Settings::seperatorSection($settings_index, 'categories');
            Settings::createAdvancedWebhookSection($settings_index);
        }

        // --------------------------------------------------------------------
        // Search Tab Creation
        // --------------------------------------------------------------------
        protected function createSearchTab()
        {
            $settings_key = $this->search_settings_key;
            $this->plugin_settings_tabs[$settings_key] = 'Search';
            register_setting($settings_key, $settings_key);

            // Setting Default options values.
            $settings = get_option($settings_key);

            $settings_index = 'search';

            if (empty($settings)) {
                //Setup defaults
            }

            $this->settings[$settings_index] = $settings;
            update_option($settings_key, $settings);

            Settings::createSearchSection($settings_index);

        }

        // --------------------------------------------------------------------
        // Uninstall Tab Creation
        // --------------------------------------------------------------------
        protected function createUninstallTab()
        {
            $settings_key = $this->uninstall_settings_key;
            $this->plugin_settings_tabs[$settings_key] = 'Uninstall';
            register_setting($settings_key, $settings_key);

            $settings_index = 'uninstall';

            Settings::createUninstallSection($settings_index);
        }

        // --------------------------------------------------------------------
        // Start Sections Creation
        // --------------------------------------------------------------------
        protected function seperatorSection($settings_key, $sep_id)
        {
            add_settings_section(
                'admwpp_seperator_section admwpp_' . $sep_id,
                '',
                array($this, 'seperatorSettingsSection'),
                "admwpp_" . $settings_key . "_settings"
            );
        }

        /** ACCOUNT SECTION */
        protected function createAccountSection($settings_key)
        {
            global $ADMWPP_APP_ENVIRONMENT;
            $environment_url = $ADMWPP_APP_ENVIRONMENT[ADMWPP_ENV]['administrate'];

            add_settings_section(
                'admwpp_account_section',
                "<span class='admwpp-section-title'>" . __('Connect WP With Core APIs', ADMWPP_TEXT_DOMAIN) . "</span>",
                array($this, 'accountSettingsSection'),
                "admwpp_" . $settings_key . "_settings"
            );

            add_settings_field(
                'admwpp-instance',
                __('Administrate Instance:', ADMWPP_TEXT_DOMAIN),
                array($this, 'settingsFieldInput'),
                "admwpp_" . $settings_key . "_settings",
                'admwpp_account_section',
                array(
                    'field'        => 'instance',
                    'settings_key' => $settings_key,
                    'placeholder'  => 'Instance',
                    'info'         => '<i>' . __('Instance Url to connect to', ADMWPP_TEXT_DOMAIN) . '</i>',
                )
            );

            add_settings_field(
                'admwpp-app-id',
                __('Administrate APP ID:', ADMWPP_TEXT_DOMAIN),
                array($this, 'settingsFieldInput'),
                "admwpp_" . $settings_key . "_settings",
                'admwpp_account_section',
                array(
                    'field'        => 'app_id',
                    'settings_key' => $settings_key,
                    'placeholder'  => 'APP ID',
                    'info'         => '<i>' . __('You must enter a valid Administrate APP ID.', ADMWPP_TEXT_DOMAIN) . '<br/>' . __('If you do not have an API key, you can get one by clicking', ADMWPP_TEXT_DOMAIN) . ' <a href="' . $environment_url . '" target="_blank" title="'. __('Administrate Accounts', ADMWPP_TEXT_DOMAIN) . '">' . __('here', ADMWPP_TEXT_DOMAIN) . '</a>.</i>',
                )
            );

            add_settings_field(
                'admwpp-app-secret',
                __('Administrate APP Secret:', ADMWPP_TEXT_DOMAIN),
                array($this, 'settingsFieldInput'),
                "admwpp_" . $settings_key . "_settings",
                'admwpp_account_section',
                array(
                    'field'        => 'app_secret',
                    'settings_key' => $settings_key,
                    'placeholder'  => 'APP Secret',
                    'info'         => '<i>' . __('You must enter a valid Administrate API secret.', ADMWPP_TEXT_DOMAIN) . '</i>',
                )
            );
        }

        protected function createAccountWeblinkSection($settings_key)
        {
            add_settings_section(
                'admwpp_account_weblink_section',
                "<span class='admwpp-section-title'>" . __('Connect WP With Weblink APIs', ADMWPP_TEXT_DOMAIN) . "</span>",
                array(),
                "admwpp_" . $settings_key . "_settings"
            );

            add_settings_field(
                'admwpp-portal',
                __('Administrate Portal:', ADMWPP_TEXT_DOMAIN),
                array($this, 'settingsFieldInput'),
                "admwpp_" . $settings_key . "_settings",
                'admwpp_account_weblink_section',
                array(
                    'field'        => 'portal',
                    'settings_key' => $settings_key,
                    'placeholder'  => 'Portal',
                    'info'         => '<i>' . __('Portal Url to connect to', ADMWPP_TEXT_DOMAIN) . '</i>',
                )
            );
        }
        /** END ACCOUNT SECTION */

        /** Search SECTION */
        protected function createSearchSection($settings_key)
        {
            add_settings_section(
                'admwpp_search_section',
                "<span class='admwpp-section-title'>" . __('Search Filters', ADMWPP_TEXT_DOMAIN) . "</span>",
                array(),
                "admwpp_" . $settings_key . "_settings"
            );

            add_settings_field(
                'admwpp_date_filter_section',
                __('Date Filters', ADMWPP_TEXT_DOMAIN),
                array($this, 'settingsFieldInputBoolean'),
                "admwpp_" . $settings_key . "_settings",
                'admwpp_search_section',
                array(
                    'field'        => 'date_filters',
                    'settings_key' => $settings_key,
                    'placeholder'  => 'Date Filters',
                    'info'         => '<p>' . __('This will Disable / Enable Date Filters in the search page', ADMWPP_TEXT_DOMAIN) . '</p>',
                )
            );
            add_settings_field(
                'admwpp_locations_filters_section',
                __('Locations Filters', ADMWPP_TEXT_DOMAIN),
                array($this, 'settingsFieldInputBoolean'),
                "admwpp_" . $settings_key . "_settings",
                'admwpp_search_section',
                array(
                    'field'        => 'locations_filters',
                    'settings_key' => $settings_key,
                    'placeholder'  => 'Locations Filters',
                    'info'         => '<p>' . __('This will Disable / Enable Locations Filters in the search page', ADMWPP_TEXT_DOMAIN) . '</p>',
                )
            );
        }

        /** END Search SECTION */

        /** LANGUAGE SECTION */
        protected function createLanguageSection($settings_key)
        {
            global $ADMWPP_LANG;

            add_settings_section(
                'admwpp_language_settings',
                "<span class='admwpp-section-title'>" . __('Language', ADMWPP_TEXT_DOMAIN) . "</span>",
                array($this, 'languageSettingsSection'),
                "admwpp_" . $settings_key . "_settings"
            );

            add_settings_field(
                'admwpp_language_settings',
                __('Language', ADMWPP_TEXT_DOMAIN) . ':',
                array($this, 'settingsFieldSelect'),
                "admwpp_" . $settings_key . "_settings",
                'admwpp_language_settings',
                array(
                    'field'        => 'language',
                    'settings_key' => $settings_key,
                    'options'      => $ADMWPP_LANG,
                    'disabled'     => '',
                    'info'         => '',
                    'allow_null'   => false,
                )
            );
        }
        /** END LANGUAGE SECTION */

        /** STYLING SECTION */
        protected function createStylingSection($settings_key)
        {

            add_settings_section(
                'admwpp_styling_settings',
                "<span class='admwpp-section-title'>" . __('Styling', ADMWPP_TEXT_DOMAIN) . "</span>",
                array($this, 'stylingSettingsSection'),
                "admwpp_" . $settings_key . "_settings"
            );

            add_settings_field(
                'admwpp_styles_settings',
                __('Styles', ADMWPP_TEXT_DOMAIN) . ':',
                array($this, 'settingsFieldInputBoolean'),
                "admwpp_" . $settings_key . "_settings",
                'admwpp_styling_settings',
                array(
                    'field'        => 'styles',
                    'settings_key' => $settings_key,
                    'info'         => 'Selecting this checkbox will disable selectric JS library and plugin styles.
                    <br />Plugin is using selectric library v1.13.0.',
                )
            );
        }
        /** END STYLING SECTION */

        /** ADVANCED Synch SECTION */
        protected function createAdvancedSynchCoursesSection($settings_key)
        {
            add_settings_section(
                'admwpp_advanced_synch_courses_section',
                "<span class='admwpp-section-title'>" . __('Courses Synchronization', ADMWPP_TEXT_DOMAIN) . "</span>",
                array($this, 'synchSection'),
                "admwpp_" . $settings_key . "_settings"
            );

            add_settings_field(
                'admwpp_synch_title',
                __('Synch title on Update', ADMWPP_TEXT_DOMAIN),
                array($this, 'settingsFieldInputBoolean'),
                "admwpp_" . $settings_key . "_settings",
                'admwpp_advanced_synch_courses_section',
                array(
                    'field'        => 'synch_title',
                    'settings_key' => $settings_key,
                    'info'         => __('This will Disable / Enable Synching the Title of course templates once it gets updated using webhooks', ADMWPP_TEXT_DOMAIN),
                )
            );

            add_settings_field(
                'admwpp_synch_description',
                __('Synch Description on Update', ADMWPP_TEXT_DOMAIN),
                array($this, 'settingsFieldInputBoolean'),
                "admwpp_" . $settings_key . "_settings",
                'admwpp_advanced_synch_courses_section',
                array(
                    'field'        => 'synch_description',
                    'settings_key' => $settings_key,
                    'info'         => __('This will Disable / Enable Synching the Description of course templates once it gets updated using webhooks', ADMWPP_TEXT_DOMAIN),
                )
            );

            add_settings_section(
                'admwpp_advanced_synch_courses_action',
                "<span class='admwpp-section-title'>" . __('Courses Synchronization', ADMWPP_TEXT_DOMAIN) . "</span>",
                array($this, 'synchCoursesSection'),
                "admwpp_" . $settings_key . "_settings"
            );

            add_settings_section(
                'admwpp_advanced_synch_lerning_path_action',
                "<span class='admwpp-section-title'>" . __('Learning Path Synchronization', ADMWPP_TEXT_DOMAIN) . "</span>",
                array($this, 'synchLearnignPathSection'),
                "admwpp_" . $settings_key . "_settings"
            );
        }

        protected function createAdvancedSynchCategoriesSection($settings_key)
        {
            add_settings_section(
                'admwpp_advanced_synch_categories_action',
                "<span class='admwpp-section-title'>" . __('Learning Categories Synchronization', ADMWPP_TEXT_DOMAIN) . "</span>",
                array($this, 'synchCategoriesSection'),
                "admwpp_" . $settings_key . "_settings"
            );
        }

        protected function createAdvancedWebhookSection($settings_key)
        {
            add_settings_section(
                'admwpp_advanced_webhook_action',
                "<span class='admwpp-section-title'>" . __('Setup Webhooks for Synchronization', ADMWPP_TEXT_DOMAIN) . "</span>",
                array($this, 'synchWebHookSection'),
                "admwpp_" . $settings_key . "_settings"
            );

            add_settings_field(
                'admwpp-course-webhook-type-id',
                __('Course Update Webhook Type ID:', ADMWPP_TEXT_DOMAIN),
                array($this, 'settingsFieldInput'),
                "admwpp_" . $settings_key . "_settings",
                'admwpp_advanced_webhook_action',
                array(
                    'field'        => 'courses_webhook_type_id',
                    'settings_key' => $settings_key,
                    'placeholder'  => 'WEBHOOK Type ID',
                    'info'         => '<i>' . __('You must enter a valid Administrate <strong>WEBHOOK Type ID</strong>.', ADMWPP_TEXT_DOMAIN) . '</i>',
                )
            );

            add_settings_field(
                'admwpp-course-webhook-id',
                __('Saved Courses Webhook ID:', ADMWPP_TEXT_DOMAIN),
                array($this, 'settingsFieldInput'),
                "admwpp_" . $settings_key . "_settings",
                'admwpp_advanced_webhook_action',
                array(
                    'field'        => 'courses_webhook_id',
                    'settings_key' => $settings_key,
                    'placeholder'  => 'WEBHOOK ID',
                    'info'         => '<i>' . __('If you already created the webhook config add the TMS ID for it', ADMWPP_TEXT_DOMAIN) . '<br/>' . __('If not leave it blank and hit the <strong>"save settings"</strong> button the plugin will automatically create the webhook config for the <strong>webhook type ID</strong> inputed above and set it up.', ADMWPP_TEXT_DOMAIN) . '</i>'
                )
            );

            add_settings_field(
                'admwpp-lp-webhook-type-id',
                __('Learning Paths Update Webhook Type ID:', ADMWPP_TEXT_DOMAIN),
                array($this, 'settingsFieldInput'),
                "admwpp_" . $settings_key . "_settings",
                'admwpp_advanced_webhook_action',
                array(
                    'field'        => 'lp_webhook_type_id',
                    'settings_key' => $settings_key,
                    'placeholder'  => 'WEBHOOK Type ID',
                    'info'         => '<i>' . __('You must enter a valid Administrate <strong>WEBHOOK Type ID</strong>.', ADMWPP_TEXT_DOMAIN) . '</i>',
                )
            );

            add_settings_field(
                'admwpp-lp-webhook-id',
                __('Saved Learning Paths Webhook ID:', ADMWPP_TEXT_DOMAIN),
                array($this, 'settingsFieldInput'),
                "admwpp_" . $settings_key . "_settings",
                'admwpp_advanced_webhook_action',
                array(
                    'field'        => 'lp_webhook_id',
                    'settings_key' => $settings_key,
                    'placeholder'  => 'WEBHOOK ID',
                    'info'         => '<i>' . __('If you already created the webhook config add the TMS ID for it', ADMWPP_TEXT_DOMAIN) . '<br/>' . __('If not leave it blank and hit the <strong>"save settings"</strong> button the plugin will automatically create the webhook config for the <strong>webhook type ID</strong> inputed above and set it up.', ADMWPP_TEXT_DOMAIN) . '</i>'
                )
            );
        }
        /** END ADVANCED Synch SECTION */

        /** UNISTALL SECTION */
        protected function createUninstallSection($settings_key)
        {
            add_settings_section(
                'admwpp_uninstall_section',
                "<span class='admwpp-section-title'>" . __('Uninstall Settings', ADMWPP_TEXT_DOMAIN) . "</span>",
                array($this, 'uninstallSettingsSection'),
                "admwpp_" . $settings_key . "_settings"
            );

            add_settings_field(
                'admwpp_remove_everything',
                __('Remove everything', ADMWPP_TEXT_DOMAIN),
                array($this, 'settingsFieldInputBoolean'),
                "admwpp_" . $settings_key . "_settings",
                'admwpp_uninstall_section',
                array(
                    'field'        => 'remove_everything',
                    'settings_key' => $settings_key,
                    'info'         => '(Removing everything will remove all saved settings params)',
                )
            );
        }
        /** END UNISTALL SECTION */

        // --------------------------------------------------------------------
        // END Section Creation
        // --------------------------------------------------------------------

        // --------------------------------------------------------------------
        // Section Helper Text
        // --------------------------------------------------------------------
        public function seperatorSettingsSection($args)
        {
            $section_id = $args['id'];
            echo "<hr class='$section_id'>";
        }


        public function accountSettingsSection()
        {
            global $ADMWPP_APP_ENVIRONMENT;
            $environment_url = $ADMWPP_APP_ENVIRONMENT[ADMWPP_ENV]['administrate'];

            if (Main::active()) {
                // Think of this as help text for the section.
                echo '<p>' . __('To modify your Administrate Account Settings', ADMWPP_TEXT_DOMAIN) . ', <a href="'.$environment_url.'" target="_blank" title="'. __('Administrate Accounts', ADMWPP_TEXT_DOMAIN) . '">' . __('click here', ADMWPP_TEXT_DOMAIN) . '</a>.</p>';
            } else {
                // Think of this as help text for the section.
                echo '<p>' . __('You must be an Administrate Partner to use the plugin.', ADMWPP_TEXT_DOMAIN) . '<p>';
                echo '<p>' . __('By becoming an Administrate Partner, you will have access to Administrate services such as Administrate Search, Administrate Lists, and Checkout.', ADMWPP_TEXT_DOMAIN) . '</p>';
                echo '<p>' . __('To set up your Administrate account', ADMWPP_TEXT_DOMAIN) . ', <a href="'.$environment_url.'" target="_blank" title="'. __('Administrate Accounts', ADMWPP_TEXT_DOMAIN) . '">' . __('click here', ADMWPP_TEXT_DOMAIN) . '</a></p>';
            }
        }

        public function uninstallSettingsSection()
        {
            // Think of this as help text for the section.
            echo "<div class='settings-section-info'>";
            echo __('Specify the data that you wish to uninstall.', ADMWPP_TEXT_DOMAIN);
            echo "</div>";
        }

        public function languageSettingsSection()
        {
           // Think of this as help text for the section.
            echo "<div class='settings-section-info'>";
            echo __("Set the language of your website below.", ADMWPP_TEXT_DOMAIN);
            echo "</div>";
        }

        public function stylingSettingsSection()
        {
           // Think of this as help text for the section.
            echo "<div class='settings-section-info'>";
            echo __("Disable plugins third party libraries (CSS/JS).", ADMWPP_TEXT_DOMAIN);
            echo "</div>";
        }

        public function synchSection()
        {
            // Think of this as help text for the section.
            echo "<div class='settings-section-info'>";
            echo __('Options to Setup Courses Synchronization Parameters.', ADMWPP_TEXT_DOMAIN);
            echo "</div>";
        }

        public function synchCategoriesSection()
        {
            echo "<div class='settings-section-info'>";
            echo __('Import Learning Categories.', ADMWPP_TEXT_DOMAIN);
            echo "</div>";
            echo "<a href='javascript:void(0);' class='button admwpp-settings-button'
            id='admwpp-import-categories-button' title='" .
            __('Import', ADMWPP_TEXT_DOMAIN) . "' data-per_page='20' data-page='1' data-imported='0' data-exists='0'>
            <i class='fa fa-clone'></i>" .
            __('Import', ADMWPP_TEXT_DOMAIN);
            echo "<i class='fa fa-refresh fa-spin admwpp_spinner'></i>";
            echo "</a>";
            echo "<div class='settings-section-info' id='admwpp-import-info-categories'></div>";
        }

        public function synchWebHookSection()
        {
            echo "<div class='settings-section-info'>";
            echo __('Create Webhooks.', ADMWPP_TEXT_DOMAIN);
            echo "</div>";
        }

        public function synchCoursesSection()
        {
            echo "<div class='settings-section-info'>";
            echo __('Import Courses.', ADMWPP_TEXT_DOMAIN);
            echo "</div>";
            echo "<a href='javascript:void(0);' class='button admwpp-settings-button'
            id='admwpp-import-courses-button' title='" .
            __('Import', ADMWPP_TEXT_DOMAIN) . "' data-per_page='5' data-page='1' data-imported='0' data-exists='0'>
            <i class='fa fa-clone'></i>" .
            __('Import', ADMWPP_TEXT_DOMAIN);
            echo "<i class='fa fa-refresh fa-spin admwpp_spinner'></i>";
            echo "</a>";
            echo "<div class='settings-section-info' id='admwpp-import-info-courses'></div>";
        }

        public function synchLearnignPathSection()
        {
            echo "<div class='settings-section-info'>";
            echo __('Import Learning Paths.', ADMWPP_TEXT_DOMAIN);
            echo "</div>";
            echo "<a href='javascript:void(0);' class='button admwpp-settings-button'
            id='admwpp-import-learning-path-button' title='" .
            __('Import', ADMWPP_TEXT_DOMAIN) . "' data-per_page='5' data-page='1' data-imported='0' data-exists='0'>
            <i class='fa fa-clone'></i>" .
            __('Import', ADMWPP_TEXT_DOMAIN);
            echo "<i class='fa fa-refresh fa-spin admwpp_spinner'></i>";
            echo "</a>";
            echo "<div class='settings-section-info' id='admwpp-import-info-learning-path'></div>";
        }
        // --------------------------------------------------------------------
        // END Section Helper Text
        // --------------------------------------------------------------------

        // --------------------------------------------------------------------
        // Get All allowed Post Types.
        // --------------------------------------------------------------------
        public static function getPostTypes()
        {
            global $ADMWPP_EXCLUDED_POST_TYPES;
            $post_types = get_post_types();
            $post_types = array_diff($post_types, $ADMWPP_EXCLUDED_POST_TYPES);
            // Get the Post Types labels.
            foreach ($post_types as $key => $value) {
                $type_obj = get_post_type_object($key);
                $label    = ucfirst($type_obj->labels->name);
                $post_types[$key] = $label;
            }
            return $post_types;
        }

        // --------------------------------------------------------------------
        // Get App Environments.
        // --------------------------------------------------------------------
        public static function getAppEnvironments()
        {
            global $ADMWPP_APP_ENVIRONMENT;
            $app_environments = $ADMWPP_APP_ENVIRONMENT;
            foreach ($app_environments as $key => $value) {
                $app_environments[$key] = $value['label'];
            }
            return $app_environments;
        }

        // --------------------------------------------------------------------
        // Validation Callbacks
        // --------------------------------------------------------------------
        public function optionsValidateumeric($input)
        {
            if (is_numeric($input) && $input != 0) {
                return $input;
            } else {
                return "";
            }
        }

        // --------------------------------------------------------------------
        // Building Settings Form Fields
        // --------------------------------------------------------------------
        public function settingsColorPickerFields($args)
        {
            global $ADMWPP_COLOR_FIELDS;

            $field_key    = $args['field'];
            $settings_key = $args['settings_key'];
            $section_key  = $args['section_key'];
            $options      = $args['options'];
            $per_column   = $args['per_column'];
            $info         = $args['info'];

            // Get the value of this setting
            $value   = '';
            $section = $this->getSettingsOption($settings_key, $section_key);

            $settings_key = "admwpp_" . $settings_key . "_settings";

            echo "<table class='form-table admwpp-settings-table'>";
            echo "<tbody><tr>";

            $options_chunks = array_chunk(array_keys($options), $per_column);

            foreach ($options_chunks as $chunk) {
                echo "<td>";
                echo "<table class='form-table'>";
                foreach ($chunk as $key) {
                    $id     = "admwpp_" . $key;
                    $name   = $settings_key . "[$section_key][$key]";
                    $value  = "";
                    $label  = $ADMWPP_COLOR_FIELDS[$key];

                    if (isset($section[$key])) {
                        $value = $section[$key];
                    }

                    echo "<tr class='field-wrapper field-color'>";

                    echo "<td class='label'><label>". __($label, ADMWPP_TEXT_DOMAIN) . "</label></td>";
                    echo "<td><input class='admwpp-settings-text admwpp_color_picker' type='text' name='$name' id='$id' value='$value' autocomplete='off'/></td>";

                    echo "</tr>";
                }
                echo "</table>";
                echo "</td>";
            }
            echo "</tr></tbody>";
            echo "</table>";

            if ($info) {
                echo "<div class='admwpp-field-info'>" . __($info, ADMWPP_TEXT_DOMAIN) . "</div>";
            }
        }

        public function settingsFieldInput($args)
        {
            // Get the field settings key, key and options from the $args array
            $field_key    = $args['field'];
            $settings_key = $args['settings_key'];
            $placeholder  = $args['placeholder'];
            $info         = $args['info'];

            $disabled = "";
            if (isset($args['disabled'])) {
                $disabled = 'disabled';
            }

            if (isset($args['value'])) {
                $value = $args['value'];
            } else {
                // Get the value of this setting
                $value = $this->getSettingsOption($settings_key, $field_key);
            }


            if (is_array($value)) {
                $value = json_encode($value);
            }

            $settings_key = "admwpp_" . $settings_key . "_settings";

            $type = 'text';

            if (isset($args['type']) && !empty($args['type'])) {
                $type = $args['type'];
            }

            // echo a proper input type="text"
            $id   = "admwpp_" . $field_key;
            $name = $settings_key . "[" . $field_key . "]";
            $value = htmlspecialchars($value);
            echo "<input class='admwpp-settings-text' type='$type' name='$name' id='$id' value='$value' placeholder='$placeholder' autocomplete='off' $disabled/>";

            if ($info) {
                echo "<div class='admwpp-field-info'>" . __($info, ADMWPP_TEXT_DOMAIN) . "</div>";
            }
        }

        public function settingsFieldText($args)
        {
            // Get the field settings key, key and options from the $args array
            $field_key    = $args['field'];
            $settings_key = $args['settings_key'];
            $placeholder  = $args['placeholder'];
            $info         = $args['info'];

            $disabled = "";
            if (isset($args['disabled'])) {
                $disabled = 'disabled';
            }

            // Get the value of this setting
            $value = $this->getSettingsOption($settings_key, $field_key);

            if (is_array($value)) {
                $value = json_encode($value);
            }

            $settings_key = "admwpp_" . $settings_key . "_settings";

            // echo a proper input type="text"
            $id   = "admwpp_" . $field_key;
            $name = $settings_key . "[" . $field_key . "]";
            $value = htmlspecialchars($value);
            echo "<textarea placeholder='$placeholder' class='admwpp-settings-textarea' id='$id' name='$name' autocomplete='off' rows='5' $disabled>$value</textarea>";

            if ($info) {
                echo "<div class='admwpp-field-info'>" . __($info, ADMWPP_TEXT_DOMAIN) . "</div>";
            }
        }

        public function settingsFieldSelect($args)
        {
            // Get the field settings key, key and options from the $args array
            $field_key    = $args['field'];
            $settings_key = $args['settings_key'];
            $options      = $args['options'];
            $disabled     = $args['disabled'];
            $info         = $args['info'];
            $allow_null   = $args['allow_null'];

            $multiple = false;
            if (isset($args['multiple'])) {
                $multiple = $args['multiple'];
            }

            // Get the value of this setting
            $value = $this->getSettingsOption($settings_key, $field_key);

            $settings_key = "admwpp_" . $settings_key . "_settings";

            if (!empty($settings[$field_key])) {
                $value = $settings[$field_key];
            }

            $name = $settings_key ."[$field_key]";

            if ($multiple) {
                $multiple = 'multiple';
                $name .= "[]";
            }

            echo "<select class='admwpp-settings-list $disabled' name='$name' id='admwpp_$field_key' autocomplete='off' $disabled $multiple>";

            if ($allow_null) {
                echo "<option value=''>---</option>";
            }

            foreach ($options as $key => $option) {
                if ('currency' == $field_key) {
                    $option = $option['value'];
                }

                if ('font' == $field_key) {
                    $option = $option[0];
                }

                $selected = '';
                if ($key == $value) {
                    $selected = 'selected';
                }

                if ($multiple) {
                    if (in_array($key, $value)) {
                        $selected = 'selected';
                    }
                }

                echo "<option value='".$key."' $selected>" . __($option, ADMWPP_TEXT_DOMAIN) . "</option>";
            }
            echo "</select>";

            if ($info) {
                echo "<div class='admwpp-field-info'>" . __($info, ADMWPP_TEXT_DOMAIN) . "</div>";
            }
        }

        public function settingsFieldInputBoolean($args)
        {
            // Get the field settings key and field key from the $args array
            $field_key    = $args['field'];
            $settings_key = $args['settings_key'];
            $info         = $args['info'];

            // Get the value of this setting
            $value = $this->getSettingsOption($settings_key, $field_key);

            $settings_key = "admwpp_" . $settings_key . "_settings";

            // Check if the value is true or false
            $checked = ($value == "1") ? "checked" : "";

            // echo a proper input type="text"
            echo "<input type='checkbox' name='".$settings_key."[$field_key]' id='admwpp_$field_key' value='1' autocomplete='off' $checked/>";

            if ($info) {
                echo "<div class='admwpp-field-info'>" . __($info, ADMWPP_TEXT_DOMAIN) . "</div>";
            }
        }

        public function settingsFieldMultipleSelect($args)
        {
            // Get the field settings key and field key from the $args array
            $field_key    = $args['field'];
            $settings_key = $args['settings_key'];
            $section_key  = $args['section_key'];
            $options      = $args['options'];
            $info         = $args['info'];
            $per_column   = $args['per_column'];
            $type         = $args['type'];

            $default_lang = admwpp_primary_language();

            $edit_labels = "";
            if (isset($args['edit_labels'])) {
                $edit_labels = $args['edit_labels'];
            }

            $table_id = "admwpp-" . $settings_key . "-" . $field_key;

            // Get the value of this setting
            $saved_keys = array();
            if ($section_key) {
                $table_id .= "-" .  $section_key;
                $saved_keys = $this->getSettingsOption($settings_key, $section_key);
                if (isset($saved_keys[$field_key])) {
                    $saved_keys = $saved_keys[$field_key];
                }
                if ($edit_labels) {
                    $saved_keys = $this->getSettingsOption($settings_key, $section_key);
                    if (isset($saved_keys["labels_" . $field_key])) {
                        $labels_saved_values = $saved_keys["labels_" . $field_key];
                    }
                }
            } else {
                $saved_keys = $this->getSettingsOption($settings_key, $field_key);
                if ($edit_labels) {
                    $labels_saved_values = $this->getSettingsOption($settings_key, "labels_" . $field_key);
                }
            }

            if (empty($saved_keys)) {
                $saved_keys = array();
            }

            $settings_key = "admwpp_" . $settings_key . "_settings";

            $count          = 0;
            $options_chunks = array_chunk(array_keys($options), $per_column);

            echo "<table class='form-table admwpp-settings-table' id='$table_id' data-per-column='$per_column'>";
            echo "<tbody><tr>";

            foreach ($options_chunks as $chunk) {
                echo "<td>";
                foreach ($chunk as $value) {
                    if (is_array($saved_keys)) {
                        $checked = in_array($value, $saved_keys) ? "checked" : "";
                    } else {
                        $checked = ($value == $saved_keys) ? "checked" : "";
                    }

                    $name  = $settings_key . "[$field_key]";
                    $id    = "admwpp_$field_key";
                    $class = "admwpp_$field_key";

                    if ($edit_labels) {
                        $labels_name  = $settings_key . "[labels_$field_key]";
                        $labels_id    = "admwpp_labels_$field_key";
                        $labels_class = "admwpp_labels_$field_key";
                    }

                    if ($section_key) {
                        $name  = $settings_key . "[$section_key][$field_key]";
                        $id    = "admwpp_" . $section_key . "_" . $field_key;
                        $class = "admwpp_" . $section_key . "_" . $field_key;

                        if ($edit_labels) {
                            $labels_name  = $settings_key . "[$section_key][labels_$field_key]";
                            $labels_id    = "admwpp_" . $section_key . "_labels_" . $field_key;
                            $labels_class = "admwpp_" . $section_key . "_labels_" . $field_key;
                        }
                    }


                    if ("checkbox" == $type) {
                        if ($field_key === 'categories') {
                            $name .= "[$value]";
                            $id   .= "_" . $value;
                        } else {
                            $name .= "[$count]";
                            $id   .= "_" . $count;
                        }

                        if ($edit_labels) {
                            $labels_name .= "[$default_lang][$value]";
                            $labels_id   .= "_" . $default_lang . "_" . $value;
                        }
                    }

                    if ("radio" == $type) {
                        $id      .= "_" . $count;

                        if ($edit_labels) {
                            $labels_id .= "_" . $value;
                        }
                    }

                    $label = $options[$value];

                    echo "<div class='field-wrapper'>";

                    echo "<input type='$type' name='$name'
                    id='$id' class='$class'
                    value='$value' autocomplete='off' $checked/>";

                    echo "<label for='$id'>" . __($label, ADMWPP_TEXT_DOMAIN) . "</label>";

                    if ($edit_labels) {
                        $label_value = '';

                        if (is_plugin_active(ADMWPP_WPML_PATH)) {
                            $languages = icl_get_languages();
                            foreach ($languages as $key => $lang) {
                                $lang_code = $lang['code'];

                                echo "<div class='admwpp-labels-edit-wrapper'>";

                                $labels_name_lang   = $labels_name;
                                $labels_id_lang     = $labels_id;
                                $labels_class_lang  = $labels_class;

                                if ($lang_code !== $default_lang) {
                                    $labels_name_lang    = str_replace("[$default_lang]", "[$lang_code]", $labels_name);
                                    $labels_id_lang      = str_replace("_" . $default_lang . "_", "_" . $lang_code . "_", $labels_id);
                                }

                                if (isset($labels_saved_values[$lang_code][$value])) {
                                    $label_value = $labels_saved_values[$lang_code][$value];
                                }
                                if (!$label_value) {
                                    echo "<a class='button admwpp-settings-button admwpp_settings_edit_label_button' data-id='$labels_id_lang' href='javascript:void(0);' title='" . __("Edit Label", ADMWPP_TEXT_DOMAIN) . "'><i class='fa fa-pencil-square-o'></i>" . __("Edit", ADMWPP_TEXT_DOMAIN) . " [$lang_code]</a>";
                                    $labels_class_lang .= " admwpp-hidden";
                                }
                                echo "<label id='$labels_id_lang' class='$labels_class_lang'><strong>" . ucfirst($lang_code) . "</strong> : <input type='text' name='$labels_name_lang' autocomplete='off' value='$label_value' placeholder='" . __("Label to use", ADMWPP_TEXT_DOMAIN) . "'/></label>";
                                echo "</div>";
                            }
                        } else {
                            echo "<div class='admwpp-labels-edit-wrapper'>";
                            if (isset($labels_saved_values[$default_lang][$value])) {
                                $label_value = $labels_saved_values[$default_lang][$value];
                            }
                            if (!$label_value) {
                                echo "<a class='button admwpp-settings-button admwpp_settings_edit_label_button' data-id='$labels_id' href='javascript:void(0);' title='" . __("Edit Label", ADMWPP_TEXT_DOMAIN) . "'><i class='fa fa-pencil-square-o'></i>" . __("Edit", ADMWPP_TEXT_DOMAIN) . "</a>";
                                $labels_class .= " admwpp-hidden";
                            }
                            echo "<input type='text' name='$labels_name' id='$labels_id' class='$labels_class' autocomplete='off' value='$label_value' placeholder='" . __("Label to use", ADMWPP_TEXT_DOMAIN) . "'/>";
                            echo "</div>";
                        }
                    }

                    echo "</div>";
                    $count++;
                }
                echo "</td>";
            }
            echo "</tr></tbody>";
            echo "</table>";

            if ($info) {
                echo "<div class='admwpp-field-info'>" . __($info, ADMWPP_TEXT_DOMAIN) . "</div>";
            }
        }

        // --------------------------------------------------------------------
        // END Field Input
        // --------------------------------------------------------------------

        /**
         * CALLBACK FUNCTION FOR:
         * add_action('admin_notices', array($this, 'adminWarnings'));
         *
         * @return void
         *
         * */
        public function adminWarnings()
        {
            if (!Main::active()) {
                global $hook_suffix, $current_user;
                $page = '';
                if (isset($_GET['page'])) {
                    $page = $_GET['page'];
                }
                if ($hook_suffix == 'plugins.php' || $page == 'admwpp-settings') {
                    include(ADMWPP_ADMIN_TEMPLATES_DIR . 'settings/activation-warning.php');
                }
            }
        }


        /**
         * CALLBACK FUNCTION FOR:
         * add_action('admin_menu', array($this, 'admin_menu'));
         * Deactivates Plugin
         *
         * @return void
         *
         * */
        public function adminMenu()
        {
            // Main Settings Page
            $page_title = __('Administrate Settings', ADMWPP_TEXT_DOMAIN);
            $menu_title = __('Administrate', ADMWPP_TEXT_DOMAIN);
            $capability = $this->capability;
            $menu_slug  = $this->plugin_options_key;
            $function   = array($this, 'pluginSettingsPage');

            add_menu_page(
                $page_title,
                $menu_title,
                $capability,
                $menu_slug,
                $function
            );
        }

        /**
         * FUNCTION TO CREATE SUBMENUS:
         *
         * @return void
         *
         * */
        public function createSubmenus()
        {
            foreach ($this->plugin_settings_tabs as $tab_key => $tab_caption) {
                switch ($tab_key) {
                    case 'admwpp_advanced_settings':
                    case 'admwpp_uninstall_settings':
                        $capability = "manage_options";
                        break;
                    default:
                        $capability = $this->capability;
                        break;
                }

                // General Settings Page
                $parent_slug = $this->plugin_options_key;
                $page_title  = __($tab_caption . 'Settings', ADMWPP_TEXT_DOMAIN);
                $menu_title  = __($tab_caption, ADMWPP_TEXT_DOMAIN);
                $capability  = $capability;
                $menu_slug   = "admin.php?page=" . $this->plugin_options_key . "&tab=" . $tab_key;

                add_submenu_page(
                    $parent_slug,
                    $page_title,
                    $menu_title,
                    $capability,
                    $menu_slug
                );
            }
        }

        /**
         * CALLBACK FUNCTION FOR:
         * add_menu_page();
         * Renders the Settings form
         *
         * @return void
         *
         * */
        public function pluginSettingsPage()
        {
            $tab = isset($_GET['tab']) ? $_GET['tab'] : $this->account_settings_key;

            switch ($tab) {
                case 'admwpp_advanced_settings':
                case 'admwpp_uninstall_settings':
                    $capability = "manage_options";
                    break;
                default:
                    $capability = $this->capability;
                    break;
            }

            if (!current_user_can($capability)) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            if (!Main::active() ||
                !array_key_exists($tab, $this->plugin_settings_tabs)) {
                $tab = $this->account_settings_key;
            }

            include(ADMWPP_ADMIN_TEMPLATES_DIR . 'settings/settings.php');
        }

        /*
         * Function to return Tabs Font Awesome icon.
         */
        public function pluginOptionsIcons($tab_key)
        {
            switch ($tab_key) {
                case 'admwpp_account_settings':
                    return "<i class='fa fa-user'></i>";
                    break;
                case 'admwpp_general_settings':
                    return "<i class='fa fa-cog'></i>";
                    break;
                case 'admwpp_advanced_settings':
                    return "<i class='fa fa-cogs'></i>";
                    break;
                case 'admwpp_search_settings':
                    return "<i class='fa fa-search'></i>";
                    break;
                case 'admwpp_uninstall_settings':
                    return "<i class='fa fa-trash'></i>";
                    break;
            }
        }

        /*
         * Renders our tabs in the plugin options page,
         * walks through the object's tabs array and prints
         * them one by one. Provides the heading for the
         * plugin_options_page method.
         */
        public function pluginOptionsTabs()
        {
            $current_tab = isset($_GET['tab']) ? $_GET['tab'] : $this->account_settings_key;
            echo '<div class="admwpp-settings-tabs-wrapper">';
            foreach ($this->plugin_settings_tabs as $tab_key => $tab_caption) {
                if (!current_user_can('manage_options') && in_array(
                    $tab_key,
                    array('admwpp_advanced_settings', 'admwpp_uninstall_settings')
                )
                ) {
                    continue;
                }

                $active = $current_tab == $tab_key ? 'admwpp-active' : '';
                $icon   = $this->pluginOptionsIcons($tab_key);
                echo "<a class='admwpp-nav-tab $active $tab_key' href='?page=$this->plugin_options_key&tab=$tab_key'>$icon " . __($tab_caption, ADMWPP_TEXT_DOMAIN) . "</a>";
            }
            echo '</div>';
        }

        /**
         * CALLBACK FUNCTION FOR:
         * add_filter('plugin_action_links', array($this, 'plugin_action_links'));
         * Adds Action Links to the plugin in the plugins list page
         *
         * @return $links, array
         *
         * */
        public function plugin_action_links($links)
        {
            $settings_link = array(
                'settings' => "<a href='admin.php?page=$this->plugin_options_key'>" .
                __('Settings', ADMWPP_TEXT_DOMAIN) . "</a>"
            );
            $links = array_merge($settings_link, $links);
            return $links;
        }

        /**
         * CALLBACK FUNCTION FOR:
         * add_filter('option_page_capability', array($this, 'option_page_capability'));
         * Update Settings page capabilities
         *
         * @return $capability, string
         *
         * */
        public function option_page_capability($capability)
        {
            return $this->capability;
        }
    }

    // END class Settings
}
