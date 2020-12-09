<?php
namespace ADM\WPPlugin\Api;

use ADM\WPPlugin as ADMWPP;
use ADM\WPPlugin\Oauth2;
use ADM\WPPlugin\Settings;

use Administrate\PhpSdk\Course as SDKCourse;

/**
 * Construct the "Course" post type
 * */
if (!class_exists('Search')) {

    class Search
    {
        protected static $instance;

        static $searchFields = array(
            'id',
            'code',
            'name',
            'teaserDescription',
            'description',
            'category',
            'imageUrl',
            'priceRange' => array(
                'normalPrice' => array('amount')
            )
        );

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
         * @return Search object
         * */
        public static function instance()
        {
            if (!isset(self::$instance)) {
                $className = __CLASS__;
                self::$instance = new $className;
            }
            return self::$instance;
        }

        public static function addFilters()
        {
            $class = get_called_class();
            // Custom Search Query vars
            add_filter(
                'query_vars',
                array($class, 'queryVars'),
                '10',
                1
            );
        }

        public static function addActions()
        {
        }

        public static function addShortcodes()
        {
            $class = get_called_class();
            add_shortcode(
                'admwpp-search-form',
                array($class, 'searchForm')
            );
        }

        /**
         * Add custom Query vars used on search page
         * @param  array $vars array of query vars
         * @return array       updated array of query vars
         */
        public static function queryVars($vars)
        {
            $vars[] = 'query';
            $vars[] = 'lcat';
            return $vars;
        }

        public static function searchForm()
        {
            $categories = get_terms(array(
                'taxonomy' => 'learning-category',
                'hide_empty' => true,
                'parent' => 0,
            ));

            $query = get_query_var('query');
            $lcat = get_query_var('lcat') ? get_query_var('lcat') : array();
            $page = get_query_var('page') ? (int) get_query_var('page') : 1;
            $per_page = get_query_var('per_page') ? (int) get_query_var('per_page') : ADMWPP_SEARCH_PER_PAGE;

            $params = array(
                'page' => $page,
                'per_page' => $per_page,
            );

            if ($query) {
                $params['query'] = $query;
            }
            if ($lcat) {
                $params['lcat'] = $lcat;
            }

            $searchResults = self::search($params);

            $template = self::getTemplatePath('form');
            $categoryFilterTemplate = self::getTemplatePath('category-filter');
            $courseTemplate = self::getTemplatePath('course');

            //TODO: add pager template with types (simple / full)
            //simple: current page number out of the total and prev/next= buttons
            //full: full pager with prev/next first/last buttons and page numbers
            include $template;
        }

        public static function search($params)
        {
            $activate = Oauth2\Activate::instance();
            $activate->setParams(true);
            $apiParams = $activate::$params;

            $portalToken = $activate->getAuthorizeToken(true);
            $apiParams['portalToken'] = $portalToken;
            $apiParams['portal'] = Settings::instance()->getSettingsOption('account', 'portal');

            $SDKCourse = new SDKCourse($apiParams);

            $args = array(
                'filters' => array(),
                'fields' => self::$searchFields,
                'paging' => array(
                    'page' => (int) $params['page'],
                    'perPage' => (int) $params['per_page']
                ),
                'sorting' => array(
                    'field' => 'name',
                    'direction' => 'asc'
                ),
                'returnType' => 'array', //array, obj, json
            );

            if (isset($params['query']) && !empty($params['query'])) {
                $args['filters'][] = array(
                    'field' => 'name',
                    'operation' => 'like',
                    'value' => '%' . $params['query'] . '%',
                );
            }

            if (isset($params['lcat']) && !empty($params['lcat'])) {
                $args['filters'][] = array(
                    'field' => 'categoryId',
                    'operation' => 'eq',
                    'value' => $params['lcat'],
                );
            }

            $allCourses = $SDKCourse->loadAll($args);

            $courses = $allCourses['courses'];

            $pageInfo = $courses['pageInfo'];
            $courses = $courses['edges'];

            $results = array(
                'totalRecords' => $pageInfo['totalRecords'],
                'hasNextPage' => $pageInfo['hasNextPage'],
                'hasPreviousPage' => $pageInfo['hasPreviousPage'],
                'courses' => $courses,
            );

            return $results;
        }


        /**
         * Function to return the Design HTML template path.
         *
         * @params  $template, string, template name.
         *
         * @return string, template path.
         *
         */
        public static function getTemplatePath($template)
        {
            // Active theme template overide
            $themeTemplatePath = get_stylesheet_directory() . '/' . ADMWPP_PREFIX .'/search/';

            // Default Plugin Template
            $pluginTemplatePath = ADMWPP_TEMPLATES_DIR . 'search/';

            $template = $template . ".php";

            if (file_exists($themeTemplatePath . $template)) {
                return $themeTemplatePath . $template;
            }

            return $pluginTemplatePath . $template;
        }
    }
}
