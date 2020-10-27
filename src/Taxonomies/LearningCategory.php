<?php
namespace ADM\WPPlugin\Taxonomies;

use ADM\WPPlugin\Base as Base;

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
        static $public          = false;
        static $show_in_nav_menus = false;

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
        public static function instance($post_type = '')
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
    }
}
