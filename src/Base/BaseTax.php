<?php
namespace ADM\WPPlugin\Base;

if (file_exists('../../../../../wp-load.php')) {
    require_once('../../../../../wp-load.php');
}

/**
* Setup the base class for all Taxonomies
*
* @package admwpp
*
*/
abstract class BaseTax
{

    static $post_type = "";

    static $name = "";
    static $name_plural = "";
    static $system_name = "";
    static $system_slug = "";
    static $hierarchical = true;
    static $public = true;
    static $show_ui = true;
    static $show_in_nav_menus = true;
    static $show_in_rest = true;
    static $default_terms = array();

    function __construct()
    {
        add_action('init', array($this, 'init'), 0);
    }

    /**
     * CALLBACK FUNCTION FOR:
     * add_action('init', array($this, 'init'), 0);
     * Create Custom Taxonomy
     *
     * @return void
     *
     * */
    public function init()
    {

        $class = get_called_class();

        $post_type = $class::$post_type;

        $name = $class::$name;
        $name_plural = $class::$name_plural;
        $system_name = $class::$system_name;
        $system_slug = $class::$system_slug;
        $hierarchical = $class::$hierarchical;
        $public = $class::$public;
        $show_ui = $class::$show_ui;
        $default_terms = $class::$default_terms;
        $show_in_nav_menus = $class::$show_in_nav_menus;
        $show_in_rest = $class::$show_in_rest;

        $labels = array(
            'name' => _x($name, 'Taxonomy General Name', ADMWPP_TEXT_DOMAIN),
            'singular_name' => _x($name, 'Taxonomy Singular Name', ADMWPP_TEXT_DOMAIN),
            'menu_name' => __($name_plural, ADMWPP_TEXT_DOMAIN),
            'all_items' => __($name, ADMWPP_TEXT_DOMAIN),
            'parent_item' => __("Parent $name", ADMWPP_TEXT_DOMAIN),
            'parent_item_colon' => __("Parent $name:", ADMWPP_TEXT_DOMAIN),
            'new_item_name' => __("New $name", ADMWPP_TEXT_DOMAIN),
            'add_new_item' => __("Add New $name", ADMWPP_TEXT_DOMAIN),
            'edit_item' => __("Edit $name", ADMWPP_TEXT_DOMAIN),
            'update_item' => __("Update $name", ADMWPP_TEXT_DOMAIN),
            'separate_items_with_commas' => __("Separate $name_plural with commas", ADMWPP_TEXT_DOMAIN),
            'search_items' => __("Search $name_plural", ADMWPP_TEXT_DOMAIN),
            'add_or_remove_items' => __("Add or remove $name_plural", ADMWPP_TEXT_DOMAIN),
            'choose_from_most_used' => __("Choose from the most used $name_plural", ADMWPP_TEXT_DOMAIN)
        );

        $rewrite = array(
            'slug' => $system_slug,
            'with_front' => true,
            'hierarchical' => true,
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => $hierarchical,
            'public' => $public,
            'show_ui' => $show_ui,
            'show_tagcloud' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => $show_in_nav_menus,
            'show_in_rest' => $show_in_rest,
            'show_admin_column' => true,
            'query_var' => $system_name,
            'rewrite' => $rewrite,
        );

        register_taxonomy($system_name, $post_type, $args);

        if (isset($default_terms) && !empty($default_terms)) {
            // Insert the Taxonomy Terms
            foreach ($default_terms as $term) {
                wp_insert_term(
                    $term,
                    $system_name
                );
            }
        }
    }

    /**
     * Perform necessary actions when plugin is uninstalled
     *
     * @return void
     *
     * */
    public function on_uninstall()
    {

        global $wpdb;

        $class = get_called_class();
        $system_name = $class::$system_name;

        // Get all the terms for this Taxonomy
        $query = "SELECT t.*, tt.*
            FROM $wpdb->terms AS t
            INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id
            WHERE tt.taxonomy IN ('$system_name')
            ORDER BY t.name
            ASC ;
        ";
        $terms = $wpdb->get_results($query);

        // Delete all terms belonging to this Taxonomy
        if (count($terms)) {
            foreach ($terms as $term) {
                // Delete term relationships
                $delete_query = $wpdb->prepare("DELETE
                    FROM $wpdb->term_relationships
                    WHERE term_id = %d
                    ", $term->term_id);
                $wpdb->query($delete_query);

                // Delete term
                wp_delete_term($term->term_id, $system_name);
            }
        }
    }
}
