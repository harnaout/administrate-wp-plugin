<?php
namespace ADM\WPPlugin\Taxonomies;

use ADM\WPPlugin as ADMWPP;
use ADM\WPPlugin\Base as Base;
use ADM\WPPlugin\Oauth2;
use ADM\WPPlugin\Settings;

use Administrate\PhpSdk\Category as SDKCategory;
use Administrate\PhpSdk\GraphQL\Client as SDKClient;

if (! class_exists('LearningCategory')) {

    /**
    * Setup the class to handle Course Categories
    *
    * @package default
    * */
    class LearningCategory extends Base\BaseTax
    {
        static $post_type       = 'course';
        static $name            = 'Learning Category';
        static $name_plural     = 'Learning Categories';
        static $system_name     = 'learning-category';
        static $system_slug     = 'course/learning-category';
        static $public          = true;
        static $show_in_nav_menus = true;
        static $show_in_rest = true;

        static $instance;

        static $metas = array(
          'admwpp_tms_id' => array(
                'type' => 'text',
                'label' => 'TMS ID',
                'tmsKey' => 'id',
            ),
            'admwpp_tms_legacy_id' => array(
                'type' => 'text',
                'label' => 'TMS LegacyID',
                'tmsKey' => 'legacyId',
            ),
        );

        /**
         * Static Singleton Factory Method
         * Return an instance of the current class if it exists
         * Construct a new one otherwise
         *
         * @return MinionTaxonimy object
         * */
        public static function instance()
        {

            if (!isset(self::$instance)) {
                $className = __CLASS__;
                self::$instance = new $className;
            }

            return self::$instance;
        }

        public static function registerTerms()
        {
            $args = array(
                'type' => 'string',
                'single' => true,
            );
            foreach (array_keys(self::$metas) as $key) {
                register_meta('term', $key, $args);
            }
        }

        public static function termMetasColumns($columns)
        {
            foreach (self::$metas as $key => $value) {
                $columns[$key] = __($value['label'], ADMWPP_TEXT_DOMAIN);
            }
            return $columns;
        }

        public static function termMetasCustomColumns($out, $column, $termId)
        {
            if (in_array($column, array_keys(self::$metas))) {
                $value  = $value = get_term_meta($termId, $column, true);
                if (!$value) {
                    $value = '';
                }
                $out = sprintf('<span class="admwpp-term-meta-block" style="" >%s</div>', esc_attr($value));
            }
            return $out;
        }

        public static function AddCustomMetasToForm()
        {
            wp_nonce_field(basename(__FILE__), 'admwpp_term_metas_nonce');
            foreach (self::$metas as $key => $value) {
                $label = $value['label'];
                $type = $value['type'];
                ?>
                <div class="form-field admwpp-term-meta-wrap">
                    <label for="<?php echo $key; ?>"><?php _e($label, ADMWPP_TEXT_DOMAIN); ?></label>
                    <input type="<?php echo $type; ?>" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="" class="admwpp-term-meta-field" />
                </div>
                <?php
            }
        }

        public static function EditCustomMetasToForm($term)
        {
            //The term ID
            $termId = $term->term_id;
            wp_nonce_field(basename(__FILE__), 'admwpp_term_metas_nonce');
            foreach (self::$metas as $key => $value) {
                $label = $value['label'];
                $type = $value['type'];
                $metaValue = get_term_meta($termId, $key, true);
                ?>
                <tr class="form-field admwpp-term-meta-wrap">
                    <th scope="row">
                        <label for="<?php echo $key; ?>"><?php _e($label, ADMWPP_TEXT_DOMAIN); ?></label>
                    </th>
                    <td>
                        <input type="<?php echo $type; ?>" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo esc_attr($metaValue); ?>" class="admwpp-term-meta-field"  />
                    </td>
                </tr>
                <?php
            }
        }

        public static function saveTermMetas($termId)
        {
            // verify the nonce --- remove if you don't care
            if (! isset($_POST['admwpp_term_metas_nonce']) || ! wp_verify_nonce($_POST['admwpp_term_metas_nonce'], basename(__FILE__))) {
                return;
            }
            foreach (array_keys(self::$metas) as $key) {
                $old_value  = get_term_meta($termId, $key, true);
                $new_value = isset($_POST[$key]) ? $_POST[$key] : '';

                if ($old_value && '' === $new_value) {
                    delete_term_meta($termId, $key);
                } elseif ($old_value !== $new_value) {
                    update_term_meta($termId, $key, $new_value);
                }
            }
        }

        public static function checkifExists($tmsId)
        {
            global $wpdb;
            $termsMetasTable = $wpdb->termmeta;
            $sql = "SELECT term_id FROM $termsMetasTable WHERE meta_key = %s AND meta_value = %s";
            $sql = $wpdb->prepare($sql, 'admwpp_tms_id', $tmsId);
            $termId = $wpdb->get_var($sql);
            if ($termId) {
                return (int) $termId;
            }
            return 0;
        }

        public static function getCategories($params)
        {
            $activate = Oauth2\Activate::instance();
            $apiParams = $activate::$params;

            $accessToken = $activate->getAuthorizeToken()['token'];
            $appId = Settings::instance()->getSettingsOption('account', 'app_id');
            $instance = Settings::instance()->getSettingsOption('account', 'instance');

            $apiParams['accessToken'] = $accessToken;
            $apiParams['clientId'] = $appId;
            $apiParams['instance'] = $instance;

            $SDKCategory = new SDKCategory($apiParams);

            $args = array(
                'paging' => array(
                    'page' => (int) $params['page'],
                    'perPage' => (int) $params['per_page']
                ),
                'sorting' => array(
                    'field' => 'id',
                    'direction' => 'asc'
                ),
                'fields' => array(
                    'id',
                    'legacyId',
                    'name',
                    'description',
                    'parentCategory' => array(
                        'id',
                        'legacyId',
                        'name',
                        'description'
                    ),
                ),
                'returnType' => 'array', //array, obj, json
                'coreApi' => true,
            );

            return $SDKCategory->loadAll($args);
        }

        public static function nodeToTerm($node)
        {
            $results = array(
                'imported' => 0,
                'exists' => 0
            );

            $tmsId = $node['id'];
            $name = $node['name'];
            $description = $node['description'];
            $parentCategory = $node['parentCategory'];

            $taxonomy = self::$system_name;

            $termArgs = array(
                'description' => $description,
                'slug' => sanitize_title($name),
            );

            if (!empty($parentCategory)) {
                $parentTmsId = $parentCategory['id'];
                $parentName = $parentCategory['name'];
                $parentDescription = $parentCategory['description'];
                $parentTermId = self::checkifExists($parentTmsId);
                if (!$parentTermId) {
                    $parentTerm = $self::nodeToTerm($parentCategory);
                    $parentTermId = $parentTerm['termId'];
                }
                $termArgs['parent'] = $parentTermId;
            }

            $termId = self::checkifExists($tmsId);

            if ($termId) {
                $results['exists'] = 1;
            } else {
                $term = wp_insert_term(
                    $name,
                    $taxonomy,
                    $termArgs
                );
                $termId = $term['term_id'];
                $results['imported'] = 1;

                $metas = self::$metas;
                foreach ($metas as $key => $value) {
                    $tmsKey = $value['tmsKey'];
                    update_term_meta($termId, $key, $node[$tmsKey]);
                }
            }

            if ($termId) {
                $results['termId'] = $termId;
            }

            return $results;
        }
    }
}
