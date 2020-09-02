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
    }
}
