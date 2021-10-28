<?php
namespace ADM\WPPlugin\Api;

use ADM\WPPlugin as ADMWPP;
use ADM\WPPlugin\Oauth2;
use ADM\WPPlugin\Settings;
use ADM\WPPlugin\PostTypes\Course;
use ADM\WPPlugin\Taxonomies\LearningCategory;

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
                'teaserDescription',
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
                'imageUrl',
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
            if (file_exists(ABSPATH . 'wp-load.php')) {
                require_once(ABSPATH . 'wp-load.php');
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
            $vars[] = 'minplaces';
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
                'filters' => '', // 'category,date,location,age,seat-type,more'
                'per_page' => ADMWPP_SEARCH_PER_PAGE,
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

            $query = stripcslashes(urldecode(strip_tags(get_query_var('query', ''))));
            $query = filter_var(trim($query), FILTER_SANITIZE_STRING);
            $query = str_replace("\\", "", $query);
            $lcat = self::sanitizeTmsCatIds(filter_var_array(
                get_query_var('lcat', array()),
                FILTER_SANITIZE_STRING
            ));
            $fromDate = filter_var(trim(get_query_var('from')), FILTER_SANITIZE_STRING);
            $toDate = filter_var(trim(get_query_var('to')), FILTER_SANITIZE_STRING);
            $loc = self::sanitizeLocationIds(filter_var_array(
                get_query_var('loc', array()),
                FILTER_SANITIZE_STRING
            ));
            $dayofweek = self::sanitizeDayOfWeek(filter_var_array(
                get_query_var('dayofweek', array()),
                FILTER_SANITIZE_STRING
            ));
            $timeofday = filter_var(trim(get_query_var('timeofday', '')), FILTER_SANITIZE_STRING);
            $minplaces = filter_var(trim(get_query_var('minplaces', '')), FILTER_SANITIZE_NUMBER_INT);

            $page = get_query_var('paged') ? (int) filter_var(
                trim(get_query_var('paged')),
                FILTER_SANITIZE_NUMBER_INT
            ) : 1;
            $per_page = (int) filter_var(
                trim(get_query_var('per_page', $per_page)),
                FILTER_SANITIZE_NUMBER_INT
            );

            $params = array(
                'query' => $query,
                'page' => $page,
                'per_page' => $per_page,
                'dayofweek' => $dayofweek,
                'timeofday' => $timeofday,
                'minplaces' => $minplaces,
                'config' => $args,
            );

            if ($lcat) {
                $params['lcat'] = $lcat;
            }
            if (!empty($fromDate)) {
                $params['from'] = $fromDate;
            }
            if (!empty($toDate)) {
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
            $minPlacesTemplate = self::getTemplatePath('minplaces-filter');
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
                'config' => $params['config'],
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

            if (isset($params['minplaces']) && !empty($params['minplaces'])) {
                $args['filters'][] = array(
                    'field' => 'remainingPlaces',
                    'operation' => 'ge',
                    'value' => $params['minplaces'],
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

                $summary = $course['description'];
                if (isset($course['teaserDescription']) && !empty($course['teaserDescription'])) {
                    $summary = $course['teaserDescription'];
                }
                $summary = admwppTrimText($summary, 25);

                $catalogueOutput[$course['id']] = array(
                    'postId' => $coursePostId,
                    'type' => $course['__typename'],
                    'name' => $course['name'],
                    'description' => $course['description'],
                    'summary' => $summary,
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
            $locations = get_transient('admwpp_tms_locations');

            if (!empty($locations)) {
                return $locations;
            }

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
                set_transient('admwpp_tms_locations', $locations, WEEK_IN_SECONDS);
                return $locations;
            }
            return array();
        }

        public static function getAccountAssosiations($search)
        {
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

            $gql = 'query partnerAccounts {
              accounts(filters: [
                {field: name, operation: like, value: "%' . $search . '%"}
                {field: isPartner, operation: eq, value: "true"}]) {
                pageInfo {
                  hasNextPage
                  hasPreviousPage
                  totalRecords
                }
                edges {
                  node {
                    id
                    name
                    isPartner
                  }
                }
              }
            }';

            $results = $client->runRawQuery($gql);
            $data = $results->getData();

            if (isset($data->accounts->edges)) {
                $accounts = array();
                foreach ($data->accounts->edges as $key => $edge) {
                    $accounts[$edge->node->id] = $edge->node->name;
                }
                return $accounts;
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

        public static function sanitizeTmsCatIds($lcats)
        {
            $filteredLcats = array();
            if (!empty($lcats)) {
                $allowedCats = LearningCategory::getSychedTmsIds();
                foreach ($lcats as $tmsId) {
                    if (in_array($tmsId, $allowedCats)) {
                        $filteredLcats[] = $tmsId;
                    }
                }
            }
            return $filteredLcats;
        }

        public static function sanitizeDayOfWeek($dayofweek)
        {
            $filteredDayOfWeek = array();
            if (!empty($dayofweek)) {
                global $ADMWPP_SEARCH_DAYSOFWEEK;
                foreach ($dayofweek as $value) {
                    if (in_array($value, array_keys($ADMWPP_SEARCH_DAYSOFWEEK))) {
                        $filteredDayOfWeek[] = $value;
                    }
                }
            }
            return $filteredDayOfWeek;
        }

        public static function sanitizeLocationIds($locationIds)
        {
            $filteredLocationIds = array();
            if (!empty($locationIds)) {
                $allowedLocations = self::getLocationsFilter();
                foreach ($locationIds as $value) {
                    if (in_array($value, array_keys($allowedLocations))) {
                        $filteredLocationIds[] = $value;
                    }
                }
            }
            return $filteredLocationIds;
        }
    }
}
