<?php
namespace ADM\WPPlugin\PostTypes;

use ADM\WPPlugin as ADMWPP;
use ADM\WPPlugin\Taxonomies;
use ADM\WPPlugin\Oauth2;
use ADM\WPPlugin\Settings;

use Administrate\PhpSdk\Course as SDKCourse;
use Administrate\PhpSdk\GraphQL\Client as SDKClient;

/**
 * Construct the "Design" post type
 * */
if (!class_exists('Course')) {

    /**
     * Custom Post Type class called Minions
     *
     * @package default
     *
     */
    class Course
    {
        protected static $instance;

        static $slug = 'course'; // WP post type key
        static $singular = 'Course';
        static $plural = 'Courses';
        static $has_archive = true;
        static $public = true;
        static $taxonomies = array();
        static $capabilityType = 'post';
        static $hierarchical = false;
        static $publiclyQueryable = true;
        static $supports = array(
            'title',
            'editor',
            'thumbnail',
            'excerpt',
        );

        static $metas = array(
            'admwpp_tms_id' => array(
                'type' => 'text',
                'label' => 'ID',
                'tmsKey' => 'id',
            ),
            'admwpp_tms_legacy_id' => array(
                'type' => 'text',
                'label' => 'LegacyID',
                'tmsKey' => 'legacyId',
            ),
            'admwpp_tms_code' => array(
                'type' => 'text',
                'label' => 'Code',
                'tmsKey' => 'code',
            ),
            'admwpp_tms_image_id' => array(
                'type' => 'text',
                'label' => 'Image ID',
                'tmsKey' => 'image',
            ),
            'admwpp_tms_gallery' => array(
                'type' => 'text',
                'label' => 'Image Gallery',
                'tmsKey' => 'imageGallery',
            ),
            'admwpp_tms_life_cycle_state' => array(
                'type' => 'text',
                'label' => 'lifecycleState',
                'tmsKey' => 'lifecycleState',
            ),
            'admwpp_tms_learning_categories' => array(
                'type' => 'text',
                'label' => 'learningCategories',
                'tmsKey' => 'learningCategories',
            ),
            'admwpp_tms_price' => array(
                'type' => 'text',
                'label' => 'Price (Normal)',
                'tmsKey' => 'publicPrices',
            ),
            'admwpp_tms_currency' => array(
                'type' => 'text',
                'label' => 'Currency',
                'tmsKey' => 'financialUnit',
            ),
        );

        static $inlineMetas = array();

        static $taxonomy = 'learning-category';

        function __construct()
        {
            if (file_exists('../../../../../wp-load.php')) {
                require_once('../../../../../wp-load.php');
            }

            // Add all actions and filters
            $this->addFilters();
            $this->addActions();
            $this->addShortcodes();
        }

        /**
         * Static Singleton Factory Method
         * Return an instance of the current class if it exists
         * Construct a new one otherwise
         *
         * @return BWEvents object
         * */
        public static function instance()
        {
            if (!isset(self::$instance)) {
                $className = __CLASS__;
                self::$instance = new $className;
            }

            //ADD taxonomies
            Taxonomies\LearningCategory::instance();

            return self::$instance;
        }

        /**
         * Add Custom Post Type filters
         *
         * @return void
         *
         */
        protected function addFilters()
        {
            $learningCategory = Taxonomies\LearningCategory::$system_name;
            // Learning categories Filters
            add_filter(
                'manage_edit-' . $learningCategory . '_columns',
                array('ADM\WPPlugin\Taxonomies\LearningCategory', 'termMetasColumns'),
                10,
                1
            );
            add_filter(
                'manage_' . $learningCategory . '_custom_column',
                array('ADM\WPPlugin\Taxonomies\LearningCategory', 'termMetasCustomColumns'),
                10,
                3
            );
        }

        public static function getSlug()
        {
            $class = get_called_class();
            return $class::$slug;
        }

        public static function getTitle($id)
        {
            return get_the_title($id);
        }

        /**
         * Function to return the Design HTML template path.
         *
         * @params  $layout, string, layout type.
         *
         * @return string, layout template path.
         *
         */
        public static function getTemplatePath($template)
        {
            // Active theme template overide
            $themeTemplatePath = get_stylesheet_directory() . '/' . ADMWPP_PREFIX .'/' . self::getSlug() . '/';

            // Default Plugin Template
            $pluginTemplatePath = ADMWPP_TEMPLATES_DIR . self::getSlug() . '/';

            $template = $template . ".php";

            if (file_exists($themeTemplatePath . $template)) {
                return $themeTemplatePath . $template;
            }

            return $pluginTemplatePath . $template;
        }

        /**
         * Add Custom Post Type actions
         *
         * @return void
         *
         */
        protected function addActions()
        {
            add_action('init', array($this, 'Init'));
            add_action('admin_init', array($this, 'adminInit'));
            add_action('add_meta_boxes', array($this, 'addMetaBoxes'), 10, 2);

            // Save post hook
            add_action('save_post', array($this, 'savePost'), 10, 3);

            $learningCategory = Taxonomies\LearningCategory::$system_name;
            // Learning categories Actions
            add_action(
                $learningCategory . '_add_form_fields',
                array('ADM\WPPlugin\Taxonomies\LearningCategory', 'AddCustomMetasToForm'),
                10,
                2
            );
            add_action(
                $learningCategory . '_edit_form_fields',
                array('ADM\WPPlugin\Taxonomies\LearningCategory', 'EditCustomMetasToForm'),
                10,
                2
            );
            add_action(
                'edit_' . $learningCategory,
                array('ADM\WPPlugin\Taxonomies\LearningCategory','saveTermMetas'),
                10,
                1
            );
            add_action(
                'create_' . $learningCategory,
                array('ADM\WPPlugin\Taxonomies\LearningCategory', 'saveTermMetas'),
                10,
                1
            );
        }


        /**
         * [addShortcodes description]
         */
        protected function addShortcodes()
        {
        }

        public function Init()
        {
            $this->createPostType();
            $this->addAcfFields();

            Taxonomies\LearningCategory::registerTerms();
        }

        /**
         * Register the Custom Post Type
         *
         * @return void
         *
         */
        protected function createPostType()
        {
            $labels   = array(
                'name'               => self::$plural,
                'singular_name'      => self::$singular,
                'add_new'            => 'Add New',
                'add_new_item'       => 'Add New ' . self::$singular,
                'edit_item'          => 'Edit ' . self::$singular,
                'new_item'           => 'New ' . self::$singular,
                'all_items'          => 'All ' . self::$plural,
                'view_item'          => 'View ' . self::$singular,
                'search_items'       => 'Search ' . self::$plural,
                'not_found'          => 'No ' . self::$plural . ' found',
                'not_found_in_trash' => 'No ' . self::$plural . ' found in Trash',
                'parent_item_colon'  => '',
                'menu_name'          => self::$plural,
            );

            $args = array(
                 'labels'             => $labels,
                 'public'             => self::$public,
                 'publicly_queryable' => self::$publiclyQueryable,
                 'show_ui'            => true,
                 'show_in_menu'       => true,
                 'query_var'          => false,
                 'rewrite'            => array(
                     'slug'       => self::$slug,
                     'with_front' => true
                 ),
                 'capability_type'    => self::$capabilityType,
                 'has_archive'        => self::$has_archive,
                 'hierarchical'       => self::$hierarchical,
                 'menu_position'      => null,
                 'supports'           => self::$supports,
                 'taxonomies'         => self::$taxonomies,
                 'menu_icon'          => 'dashicons-shield-alt',
             );

            register_post_type(self::$slug, $args);
        }

        /**
         * CALLBACK FUNCTION FOR:
         * add_action('admin_init', array($this, 'admin_init'));
         * Add all the admin interface related stuff for the Custom Post Type
         *
         * @return void
         *
         */
        public function adminInit()
        {
        }

        /**
         * CALLBACK FUNCTION FOR:
         * add_action('add_meta_boxes', 'add_meta_boxes');
         * Hooks the function to add meta boxes
         *
         * @return void
         *
         */
        public function addMetaBoxes($postType, $post)
        {
            if (self::getSlug() == $postType) {
                add_meta_box(
                    'admwpp-metas',
                    __('Course Metas', ADMWPP_TEXT_DOMAIN),
                    array($this, 'metasMetabox'),
                    $postType,
                    'normal',
                    'high',
                    array(
                      'info' => __('Meta Data Synched from TMS', ADMWPP_TEXT_DOMAIN),
                      'class' => get_called_class(),
                    )
                );
            }
        }

        /**
         * Render Meta Box content.
         *
         * @param WP_Post $post The post object.
         */
        public static function metasMetabox($post, $metabox)
        {
            $nonce = "admwpp-" . self::getSlug() . '-nonce';
            // Add an nonce field so we can check for it later.
            wp_nonce_field(ADMWPP_PLUGIN_NAME, $nonce);
            include(ADMWPP_ADMIN_TEMPLATES_DIR . self::getSlug() .'/metas-metabox.php');
        }

        protected function addAcfFields()
        {
        }

        /**
     * CALLBACK FUNCTION FOR:
     * add_action('save_post', array($this, 'save_post'));
     * Save the metaboxes for the Custom Post Type
     *
     * @return void
     * @author Jad khater
     **/
        public function savePost($post_id, $post, $update)
        {
            $post_type = self::getSlug();

            // pointless if $_POST is empty (this happens on bulk edit)
            if (empty($_POST)) {
                return;
            }

            // verify quick edit nonce
            if (isset($_POST[ '_inline_edit' ]) && ! wp_verify_nonce($_POST[ '_inline_edit' ], 'inlineeditnonce')) {
                return;
            }

            // verify if this is an auto save routine.
            // If it is our form has not been submitted, so we don't want to do anything
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            // don't save for revisions
            if (isset($post->post_type) && $post->post_type == 'revision') {
                return;
            }

            // Check that the user has permission to edit
            if (isset($_POST['post_type']) && $_POST['post_type'] == $post_type && current_user_can('edit_post', $post_id)) {
                switch ($_POST['action']) {
                    case 'inline-save':
                        self::saveInlineMetas($post_id);
                    default:
                        self::saveMetas($post_id, $post);
                        break;
                }
            } else {
                return;
            }
        }

        /**
         *
         * Save the custom post type inline meta
         *
         * @return void
         * @author Jad khater
         **/
        public function saveInlineMetas($postId)
        {
            foreach (self::$inlineMetas as $fieldName => $fieldType) {
                // Sanitize user input
                switch ($fieldType) {
                    case 'bool':
                        if (isset($_POST[$fieldName])) {
                            $value = $_POST[$fieldName];
                        } else {
                            $value = 0;
                        }
                        break;
                    default:
                        $value = sanitize_text_field($_POST[$fieldName]);
                        break;
                }
                // Update the post's meta field
                update_post_meta($postId, $fieldName, $value);
            }
        }

        /**
         *
         * Save the custom post type meta
         *
         * @return void
         * @author Jad khater
         **/
        public function saveMetas($postId, $post)
        {

            $postType = self::getSlug();

            $nonce = "admwpp-" . $postType . '-nonce';

            // Verify nonce to check if the user intended to change this value.
            if (!isset($_POST[$nonce]) ||
            !wp_verify_nonce($_POST[$nonce], ADMWPP_PLUGIN_NAME)) {
                return;
            }

            foreach (self::$metas as $fieldName => $field) {
                // Sanitize user input
                switch ($field['type']) {
                    case 'url':
                        $value = esc_url($_POST[$fieldName]);
                        break;
                    default:
                        $value = sanitize_text_field($_POST[$fieldName]);
                        break;
                }
                // Update the post's meta field
                update_post_meta($postId, $fieldName, $value);
            }
        }

        public static function checkifExists($tmsId)
        {
            global $wpdb;
            $postMetasTable = $wpdb->postmeta;
            $sql = "SELECT post_id FROM $postMetasTable WHERE meta_key = %s AND meta_value = %s";
            $sql = $wpdb->prepare($sql, 'admwpp_tms_id', $tmsId);
            $wpPost = $wpdb->get_results($sql);
            if (isset($wpPost[0])) {
                return (int) $wpPost[0]->post_id;
            }
            return 0;
        }

        public static function setTerms($postId, $learningCategoriesIds)
        {
            global $wpdb;
            $termMetasTable = $wpdb->termmeta;
            $sql = "SELECT term_id FROM $termMetasTable WHERE meta_key = %s AND meta_value IN (%s);";
            $sql = $wpdb->prepare(
                $sql,
                'admwpp_tms_id',
                join("','", $learningCategoriesIds)
            );
            $sql = stripcslashes($sql);
            $wpTermIds = $wpdb->get_results($sql);
            $termIds = array();
            if ($wpTermIds) {
                foreach ($wpTermIds as $term) {
                    $termIds[] = $term->term_id;
                }
            }
            wp_set_post_terms($postId, $termIds, self::$taxonomy);
        }

        public static function setImage($postId, $imageId)
        {
            // Get Image Url
            $gql = "
            query {
                downloadDocument (documentId: $imageId) {
                    url
                    document {
                      id
                      name
                      description
                    }
                }
            }";

            $activate = Oauth2\Activate::instance();
            $apiParams = $activate::$params;

            $accessToken = $activate->getAuthorizeToken()['token'];
            $appId = Settings::instance()->getSettingsOption('account', 'app_id');
            $instance = Settings::instance()->getSettingsOption('account', 'instance');

            $apiParams['accessToken'] = $accessToken;
            $apiParams['clientId'] = $appId;
            $apiParams['instance'] = $instance;

            $authorizationHeaders = SDKClient::setHeaders($apiParams);
            $client = new SDKClient($apiParams['apiUri'], $authorizationHeaders);
            $results = $client->runRawQuery($gql);
            $document = $results->getData();
            $imageURL = "";
            $imageName = "";
            $imageDesc = "";
            if ($document) {
                $imageURL = $document->downloadDocument->url;
                $imageName = $document->downloadDocument->document->name;
                $imageDesc = $document->downloadDocument->document->description;
            }

            include_once(ABSPATH . 'wp-admin/includes/admin.php');

            $image = "";
            if ($imageURL != "") {
                $file = array();
                $file['caption'] = $imageDesc;
                $file['name'] = $imageName;
                $file['tmp_name'] = download_url($imageURL);

                if (is_wp_error($file['tmp_name'])) {
                    @unlink($file['tmp_name']);
                    return $file['tmp_name']->get_error_messages();
                } else {
                    $imageArgs = array(
                        'post_title' => $imageName,
                        'post_name' => sanitize_title($imageName),
                        'post_content' => $imageDesc,
                        'meta_input' => array(
                            'admwpp_tms_id' => $imageId
                        ),
                    );

                    // Check if image already synced and use it before uploading another one
                    $imagePostId = self::checkifExists($imageId);
                    if ($imagePostId) {
                        set_post_thumbnail($postId, $imagePostId);
                    } else {
                        $attachmentId = media_handle_sideload($file, $postId, $imageName, $imageArgs);

                        if (is_wp_error($attachmentId)) {
                            @unlink($file['tmp_name']);
                            return $attachmentId->get_error_messages();
                        } else {
                            set_post_thumbnail($postId, $attachmentId);
                            return wp_get_attachment_url($attachmentId);
                        }
                    }
                }
            }
        }

        public static function getCourses($params)
        {
            $activate = Oauth2\Activate::instance();
            $apiParams = $activate::$params;

            $accessToken = $activate->getAuthorizeToken()['token'];
            $appId = Settings::instance()->getSettingsOption('account', 'app_id');
            $instance = Settings::instance()->getSettingsOption('account', 'instance');

            $apiParams['accessToken'] = $accessToken;
            $apiParams['clientId'] = $appId;
            $apiParams['instance'] = $instance;

            $SDKCourse = new SDKCourse($apiParams);

            $courseFields = array(
                'id',
                'legacyId',
                'lifecycleState',
                'code',
                'title',
                'introduction',
                'image' => array(
                    'id',
                    'name'
                ),
                'imageGallery' => array(
                    'type' => 'edges',
                    'fields' => array('id', 'name')
                ),
                'learningCategories' => array(
                    'type' => 'edges',
                    'fields' => array('id', 'legacyId', 'name'),
                ),
                'publicPrices' => array(
                    'type' => 'edges',
                    'fields' => array(
                        'amount',
                        'financialUnit' => array(
                            'name',
                            '... on Currency' => array('symbol')
                        ),
                        'region' => array(
                            'code',
                        ),
                    ),
                ),
                'customFieldValues' => array(
                    'definitionKey',
                    'value'
                ),
            );

            $args = array(
                'filters' => array(
                    array(
                        'field' => 'lifecycleState',
                        'operation' => 'eq',
                        'value' => 'published',
                    ),
                    // array(
                    //     'field' => 'code',
                    //     'operation' => 'eq',
                    //     'value' => 'TESTSYNCHWP',
                    // )
                ),
                'fields' => $courseFields,
                'paging' => array(
                    'page' => (int) $params['page'],
                    'perPage' => (int) $params['per_page']
                ),
                'sorting' => array(
                    'field' => 'id',
                    'direction' => 'asc'
                ),
                'returnType' => 'array', //array, obj, json
                'coreApi' => true,
            );

            return $SDKCourse->loadAll($args);
        }

        public static function nodeToPost($node)
        {
            $results = array(
                'imported' => 0,
                'exists' => 0
            );

            $postArgs = array(
                'post_type' => self::$slug,
                'post_title' => $node['title'],
                'post_name' => sanitize_title($node['title']),
                'post_content' => '',
                'post_status' => 'pending',
            );

            if ($node['lifecycleState'] === 'published') {
                $postArgs['post_status'] = 'publish';
            }

            $postMetas = array();
            $metas = self::$metas;
            $learningCategoriesIds = array();
            $imageId = '';
            foreach ($metas as $key => $value) {
                $tmsKey = $value['tmsKey'];

                switch ($tmsKey) {
                    case 'image':
                        $tmsValue = $node[$tmsKey]['id'];
                        $imageId = $tmsValue;
                        break;
                    case 'imageGallery':
                        $imageGallery = $node[$tmsKey]['edges'];
                        $imageGalleryString = array();
                        foreach ($imageGallery as $image) {
                            $imageGalleryString[] = $image['node'];
                        }
                        $tmsValue = json_encode($imageGalleryString);
                        break;
                    case 'learningCategories':
                        $learningCategories = $node[$tmsKey]['edges'];
                        foreach ($learningCategories as $category) {
                            $learningCategoriesIds[] = $category['node']['id'];
                        }
                        $tmsValue = implode(',', $learningCategoriesIds);
                        break;
                    case 'publicPrices':
                        $publicPrices = $node[$tmsKey]['edges'];
                        $pricesAmounts = array();
                        foreach ($publicPrices as $prices) {
                            $pricesAmounts[] = $prices['node']['amount'];
                        }
                        $tmsValue = implode(',', $pricesAmounts);
                        break;
                    case 'financialUnit':
                        $publicPrices = $node['publicPrices']['edges'];
                        $pricesCurencies = array();
                        foreach ($publicPrices as $prices) {
                            $pricesCurencies[] = $prices['node']['financialUnit']['name'] . "-" . $prices['node']['financialUnit']['symbol'];
                        }
                        $tmsValue = implode(',', $pricesCurencies);
                        break;

                    default:
                        $tmsValue = $node[$tmsKey];
                        break;
                }
                $postMetas[$key] = $tmsValue;
            }

            // Process Custom Fields
            $customFields = array();
            $customFieldValues = $node['customFieldValues'];
            foreach ($customFieldValues as $field) {
                $customFields[$field['definitionKey']] = $field['value'];
            }

            global $TMS_CUSTOM_FILEDS;
            if (!empty($TMS_CUSTOM_FILEDS) && $customFieldValues) {
                foreach ($TMS_CUSTOM_FILEDS as $key => $value) {
                    $tmsKey = $value['tmsKey'];
                    $postMetas[$key] = $customFields[$tmsKey];
                }
            }

            if ($postMetas) {
                $postArgs['meta_input'] = $postMetas;

                $postArgs['post_excerpt'] = strip_tags($postMetas[TMS_SHORT_DESCRIPTION_KEY]);

                // Post content is a contact of several custom fields from the TMS
                global $TMS_COURSE_CONTENT;
                $content = '';
                foreach ($TMS_COURSE_CONTENT as $fieldKey) {
                    $content .= $postMetas[$fieldKey];
                    $content .= "<br/>";
                }
                $postArgs['post_content'] = $content;
            }

            // Check if course exists and set ID in postArgs
            $postId = self::checkifExists($node['id']);

            if ($postId) {
                $postArgs['ID'] = $postId;
                $updated = wp_insert_post($postArgs);
                if ($updated) {
                    $results['exists'] = 1;
                }
            } else {
                $postId = wp_insert_post($postArgs);
                if ($postId) {
                    $results['imported'] = 1;
                }
            }

            // Update Post Terms
            // TODO: To be able to add new categories if not synced before
            if ($postId && $learningCategoriesIds) {
                self::setTerms($postId, $learningCategoriesIds);
            }

            // Update Post Image
            if ($postId && $imageId) {
                $results['image'] = self::setImage($postId, $imageId);
            }

            return $results;
        }
    }

// END class Minions
}
