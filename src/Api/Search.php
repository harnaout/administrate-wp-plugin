<?php
namespace ADM\WPPlugin\Api;

use ADM\WPPlugin as ADMWPP;
use ADM\WPPlugin\Oauth2;
use ADM\WPPlugin\Settings;
use ADM\WPPlugin\PostTypes\Course;

use Administrate\PhpSdk\Catalogue as SDKCatalogue;
use Administrate\PhpSdk\GraphQl\Client as SDKClient;

/**
 * Construct the "Course" post type
 * */
if (!class_exists('Search')) {

    class Search
    {
        protected static $instance;

        static $searchFields = array(
            '__typename',
            '... on Course' => array(
                'id',
                'code',
                'name',
                'description',
                'category',
                'imageUrl',
                'priceRange' => array(
                    'normalPrice' => array(
                        'amount',
                        'financialUnit' => array('symbol')
                    )
                ),
                'customFieldValues' => array(
                    'definitionKey',
                    'value'
                )
            ),
            '... on LearningPath' => array(
                'id',
                'name',
                'description',
                'lifecycleState',
                'category',
                'price' => array(
                    'amount',
                    "financialUnit" => array('symbol')
                ),
                'customFieldValues' => array(
                    'definitionKey',
                    'value'
                )
            ),
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
            $vars[] = 'from';
            $vars[] = 'to';
            $vars[] = 'loc';
            $vars[] = 'per_page';
            $vars[] = 'dayofweek';
            $vars[] = 'timeofday';
            return $vars;
        }

        public static function searchForm($args)
        {
            if (is_admin()) {
                return '';
            }
            $attsArray = array(
                'categories_filter_type' => 'select',
                'pager' => 'simple',
                'template' => 'grid',
                'filters' => '' // 'category,date,location,age,seat-type,more'
            );
            $dateSettingsOption = (int) Settings::instance()->getSettingsOption('search', 'date_filters');
            $locationSettingsOption = (int) Settings::instance()->getSettingsOption('search', 'locations_filters');
            if ($locationSettingsOption == 1) {
                $attsArray[] = 'locations_filter_type';
                $attsArray['locations_filter_type'] = 'select';
            }

            extract(
                shortcode_atts(
                    $attsArray,
                    $args
                )
            );

            $query = get_query_var('query', '');
            $lcat = get_query_var('lcat', array());
            $fromDate = get_query_var('from');
            $toDate = get_query_var('to');
            $loc = get_query_var('loc', array());
            $dayofweek = get_query_var('dayofweek', array());
            $timeofday = get_query_var('timeofday', '');

            $page = get_query_var('paged') ? (int) get_query_var('paged') : 1;
            $per_page = (int) get_query_var('per_page', ADMWPP_SEARCH_PER_PAGE);

            $params = array(
                'query' => $query,
                'page' => $page,
                'per_page' => $per_page,
                'dayofweek' => $dayofweek,
                'timeofday' => $timeofday,
            );

            if ($lcat) {
                $params['lcat'] = $lcat;
            }
            if (!empty($fromDate)) {
                $params['from'] = $fromDate;
            }
            if ($toDate) {
                $params['to'] = $toDate;
            }
            if ($loc) {
                $params['loc'] = $loc;
            }

            $searchResults = self::search($params);

            $template = self::getTemplatePath('form');
            $categoryFilterTemplate = self::getTemplatePath('category-filter');
            $dateFilterTemplate = self::getTemplatePath('date-filter');
            $locationsFilterTemplate = self::getTemplatePath('locations-filter');
            $dayOfWeekTemplate = self::getTemplatePath('dayofweek-filter');
            $timeOfDayTemplate = self::getTemplatePath('timeofday-filter');
            $courseTemplate = self::getTemplatePath('course');
            $pagerTemplate = self::getTemplatePath('pager');

            global $ADMWPP_SEARCH_DAYSOFWEEK;
            $daysOfWeekFilter = apply_filters('admwpp_days_of_week_filter', $ADMWPP_SEARCH_DAYSOFWEEK);

            global $ADMWPP_SEARCH_TIMEOFDAY;
            $timeofdayFilter = apply_filters('admwpp_time_of_day_filter', $ADMWPP_SEARCH_TIMEOFDAY);

            ob_start();
            include $template;
            $html = ob_get_contents();
            ob_end_clean();

            return $html;
        }

        public static function search($params)
        {
            $activate = Oauth2\Activate::instance();
            $activate->setParams(true);
            $apiParams = $activate::$params;

            $portalToken = $activate->getAuthorizeToken(true);
            $apiParams['portalToken'] = $portalToken;
            $apiParams['portal'] = Settings::instance()->getSettingsOption('account', 'portal');

            $SDKCatalogue = new SDKCatalogue($apiParams);

            $page = (int) $params['page'];
            $perPage = (int) $params['per_page'];

            $args = array(
                'filters' => array(),
                'customFieldFilters' => array(),
                'search' => $params['query'],
                'fields' => self::$searchFields,
                'paging' => array(
                    'page' => $page,
                    'perPage' => $perPage,
                ),
                'sorting' => array(
                    'field' => 'name',
                    'direction' => 'asc'
                ),
                'returnType' => 'array', //array, obj, json
            );

            if (isset($params['lcat']) && !empty($params['lcat'])) {
                $args['filters'][] = array(
                    'field' => 'categoryId',
                    'operation' => 'in',
                    'values' => $params['lcat'],
                );
            }

            if (isset($params['dayofweek']) && !empty($params['dayofweek'])) {
                $args['filters'][] = array(
                    'field' => 'dayOfWeek',
                    'operation' => 'in',
                    'values' => $params['dayofweek'],
                );
            }

            if (isset($params['loc']) && !empty($params['loc'])) {
                $args['filters'][] = array(
                    'field' => 'locationId',
                    'operation' => 'in',
                    'values' => $params['loc'],
                );
            }

            if (isset($params['from']) && !empty($params['from'])
                && isset($params['to']) && !empty($params['to'])) {
                $from = date('Y-m-d', strtotime($params['from']));
                $to = date('Y-m-d', strtotime($params['to']));

                $timeZoneString = wp_timezone_string();
                $today = new \DateTime("now", new \DateTimeZone($timeZoneString));
                $timezoneOffset = $today->format('P');
                $from .= "T00:00:00" . $timezoneOffset;
                $to .= "T23:59:59" . $timezoneOffset;

                $args['filters'][] = array(
                    'field' => 'start',
                    'operation' => 'ge',
                    'value' => $from,
                );
                $args['filters'][] = array(
                    'field' => 'end',
                    'operation' => 'le',
                    'value' => $to,
                );
            }

            if (isset($params['timeofday']) && !empty($params['timeofday'])) {
                // Morning: 12am-12pm
                // Afternoon: 12pm-5pm
                // Evening: 5pm-12pm
                // All day: An event that is >6 hours
                switch ($params['timeofday']) {
                    case 'morning':
                        $sessionStartTime = "00:00:00";
                        $sessionEndTime = "11:59:59";
                        break;
                    case 'afternoon':
                        $sessionStartTime = "12:00:00";
                        $sessionEndTime = "16:59:59";
                        break;
                    case 'evening':
                        $sessionStartTime = "17:00:00";
                        $sessionEndTime = "23:59:59";
                        break;
                    default:
                        $sessionStartTime = "00:00:00";
                        $sessionEndTime = "23:59:59";
                        break;
                }

                $args['filters'][] = array(
                    'field' => 'sessionStartTime',
                    'operation' => 'ge',
                    'value' => $sessionStartTime,
                );
                $args['filters'][] = array(
                    'field' => 'sessionEndTime',
                    'operation' => 'le',
                    'value' => $sessionEndTime,
                );
            }

            $args = apply_filters('admwpp_search_args', $args);

            $allCatalogue = $SDKCatalogue->loadAll($args);
            $catalogue = $allCatalogue['catalogue'];

            $pageInfo = $catalogue['pageInfo'];
            $catalogue = $catalogue['edges'];
            $catalogue = self::formatCatalogueOutput($catalogue);

            $results = array(
                'totalRecords' => $pageInfo['totalRecords'],
                'totalNumPages' => ceil($pageInfo['totalRecords'] / $perPage),
                'currentPage' => $page,
                'hasNextPage' => $pageInfo['hasNextPage'],
                'hasPreviousPage' => $pageInfo['hasPreviousPage'],
                'courses' => $catalogue,
            );

            return $results;
        }

        /**
         * Function to format catalogue output
         *
         * @param  array    $catalogue array of courses & learnings paths
         *
         * @return array    array of formatted course output
         */
        public static function formatCatalogueOutput($catalogue)
        {
            $catalogueOutput = array();
            foreach ($catalogue as $course) {
                $course = $course['node'];
                $coursePostId = Course::checkifExists($course['id']);
                $price = '';
                $symbol = '';
                $imageUrl = '';

                if (isset($course['priceRange']['normalPrice'])) {
                    $price = $course['priceRange']['normalPrice']['amount'];
                    if (isset($course['priceRange']['normalPrice']['financialUnit'])) {
                        $symbol = $course['priceRange']['normalPrice']['financialUnit']['symbol'];
                    }
                }
                if (isset($course['price']['amount'])) {
                    $price = $course['price']['amount'];
                    if (isset($course['price']['financialUnit'])) {
                        $symbol = $course['price']['financialUnit']['symbol'];
                    }
                }
                if (isset($course['imageUrl'])) {
                    $imageUrl = $course['imageUrl'];
                }

                $catalogueOutput[$course['id']] = array(
                    'postId' => $coursePostId,
                    'type' => $course['__typename'],
                    'name' => $course['name'],
                    'description' => $course['description'],
                    'imageUrl' => $imageUrl,
                    'category' => $course['category'],
                    'formattedPrice' => $symbol . ' ' . $price,
                    'price' => $price,
                    'symbol' => $symbol,
                    'customFieldValues' => $course['customFieldValues']
                );
            }

            return $catalogueOutput;
        }

        public static function getLocationsFilter()
        {

            $activate = Oauth2\Activate::instance();
            $activate->setParams(true);
            $apiParams = $activate::$params;

            $portalToken = $activate->getAuthorizeToken(true);
            $apiParams['portalToken'] = $portalToken;
            $apiParams['portal'] = Settings::instance()->getSettingsOption('account', 'portal');

            $authorizationHeaders = SDKClient::setHeaders($apiParams);
            $client = new SDKClient($apiParams['apiUri'], $authorizationHeaders);

            $gql = 'query locations {
              locations {
                edges {
                  node {
                    id
                    name
                  }
                }
              }
            }';

            $results = $client->runRawQuery($gql);
            $data = $results->getData();

            if (isset($data->locations->edges)) {
                $locations = array();
                foreach ($data->locations->edges as $key => $edge) {
                    $locations[$edge->node->id] = $edge->node->name;
                }
                return $locations;
            }
            return array();
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
