<?php
namespace ADM\WPPlugin\Builders;

use ADM\WPPlugin\PostTypes;

if(!class_exists('DbViews'))
{
  /**
   * A class to build DB views
   *
   * This class setups up views which are useful to create a table
   * that could be queried fast with all information that needs
   * to be displayed.
   *
   * @package default
   * @author jck
   **/
  class DbViews
  {
    protected static $instance;

    protected Static $prefix = ADMWPP_PREFIX . '_';
    protected Static $courseTitles = 'course_titles';
    protected Static $courseSitesCount = 'course_sites_count';

    /**
     *
     * @return void
     * @author jck
     **/
    public function __construct()
    {
    }

    /**
     * Static Singleton Factory Method
     * Return an instance of the current class if it exists
     * Construct a new one otherwise
     *
     * @return Base object
     **/
    public static function instance()
    {
        if(!isset(self::$instance)) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }

    /**
     * Setup views on install
     *
     * @return void
     * @author jck
     **/
    public static function onIstall()
    {
        self::setupPublishedCoursesTitles();
    }

    /**
     * Delete views on uninstall
     *
     * @return void
     * @author jck
     **/
    public static function onUninstall()
    {
        self::deleteViews();
    }

    /**
     * Setup views on plugin activation
     *
     * @return void
     * @author jck
     **/
    public static function onActivation()
    {
        self::onIstall();
    }

    /**
     * Update/Setup views on backfill update
     *
     * @return void
     * @author jck
     **/
    public static function onBackfill()
    {
        self::onIstall();
    }

    /**
     * Get view Table name
     *
     * @return void
     * @author jck
     **/
    protected static function getViewsTableName($name)
    {
        global $wpdb;
        $prefix = $wpdb->base_prefix . self::$prefix;
        return $prefix . $name;
    }

    /**
     * Create views for Course Titles
     *
     * @return void
     * @author jck
     **/
    protected static function setupPublishedCoursesTitles()
    {
        global $wpdb;
        $viewTableName = self::getViewsTableName(self::$courseTitles);

        $siteSql = "
        SELECT posts.`post_title` AS 'title' FROM %s AS posts
        WHERE posts.`post_type` = 'course'
        AND posts.`post_status` = 'publish'
        ";

        $postsTableName = $wpdb->base_prefix . "posts" ;
        $sitesSqlSelectString = sprintf($siteSql, $postsTableName);

        if (is_multisite()) {
            $sitesSqlSelect = array();
            $args = array(
                'archived' => 0,
                'public' => 1,
            );
            $sites = get_sites($args);
            foreach ($sites as $site)  {
                $blogId = $site->blog_id;
                $postsTableName = $wpdb->base_prefix . $blogId . "_posts" ;
                if ($blogId == 1) {
                    $postsTableName = $wpdb->base_prefix . "posts" ;
                }
                $sitesSqlSelect[] = sprintf($siteSql, $postsTableName);
            }
            $unionString = "
                UNION ALL
            ";
            $sitesSqlSelectString = implode($unionString, $sitesSqlSelect);
        }

        $query = "CREATE OR REPLACE ALGORITHM=TEMPTABLE VIEW $viewTableName AS
            SELECT distinct title FROM ($sitesSqlSelectString) $viewTableName ORDER BY title ASC;";

        $wpdb->query($query);
    }

    /**
     * Delete views tables
     *
     * @return void
     * @author jck
     **/
    protected static function deleteViews()
    {
      global $wpdb;
      $courseTitles = self::getViewsTableName(self::$courseTitles);
      $wpdb->query("DROP TABLE IF EXISTS $courseTitles");
    }

  } // END class Base
}
