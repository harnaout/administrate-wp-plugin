<?php
namespace ADM\WPPlugin\Shortcodes;

use ADM\WPPlugin as ADMWPP;
use ADM\WPPlugin\Oauth2;
use ADM\WPPlugin\Settings;
use ADM\WPPlugin\PostTypes\Course;

use Administrate\PhpSdk\Catalogue as SDKCatalogue;
use Administrate\PhpSdk\GraphQl\Client as SDKClient;

if (! class_exists('Shortcode')) {
  /**
   * Description of shortcodes
   *
   * @author
   */
    class Shortcode
    {
        protected static $instance;

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
         * @return Shortcodes object
         * */
        public static function instance()
        {
            if (!isset(self::$instance)) {
                $className = __CLASS__;
                self::$instance = new $className;
            }

            return self::$instance;
        }

        /**
         * Add Shortcodes filters
         *
         * @return void
         *
         */
        protected function addFilters()
        {
        }

        /**
         * Add Shortcodes actions
         *
         * @return void
         *
         */
        protected function addActions()
        {
            add_action("wp_ajax_addGiftVoucher", array($this, 'addGiftVoucher'));
            add_action("wp_ajax_nopriv_addGiftVoucher", array($this, 'addGiftVoucher'));

            add_action("wp_ajax_getBundledLpsAjax", array($this, 'getBundledLpsAjax'));
            add_action("wp_ajax_nopriv_getBundledLpsAjax", array($this, 'getBundledLpsAjax'));
        }

        /**
         * [addShortcodes description]
         */
        protected function addShortcodes()
        {
            add_shortcode('admwpp-gift-voucher', array($this, 'addGiftVoucherForm'));
            add_shortcode('admwpp-my-workshops', array($this, 'myWorkshops'));
            add_shortcode('admwpp-bundled-lps', array($this, 'bundledLps'));
        }

        /*
        * Process Add Gift Voucher Form
        */
        public static function addGiftVoucher()
        {
            $response = array(
                'status' => 'error',
                'message' => _x('Something went Wrong Try again please.', 'Gift Voucher', 'admwpp'),
                'response' => '',
            );
            if (isset($_POST['productOptionId']) && isset($_POST['amount']) && isset($_POST['portalToken'])) {
                $cartId = filter_var(trim($_POST['cartId']), FILTER_SANITIZE_STRING);
                $productOptionId = filter_var(trim($_POST['productOptionId']), FILTER_SANITIZE_STRING);
                $amount = (float) filter_var(trim($_POST['amount']), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                if ($amount <= 0) {
                    $response['message'] = ADMWPP_VOUCHER_EMPTY_AMOUNT_MESSAGE;
                    echo json_encode($response);
                    die();
                }

                if ($amount > ADMWPP_MAX_VOUCHER_AMOUNT) {
                    $response['message'] = ADMWPP_VOUCHER_MAX_AMOUNT_MESSAGE;
                    echo json_encode($response);
                    die();
                }

                $activate = Oauth2\Activate::instance();
                $activate->setParams(true);
                $apiParams = $activate::$params;
                $apiParams['portalToken'] = filter_var(trim($_POST['portalToken']), FILTER_SANITIZE_STRING);
                $apiParams['portal'] = filter_var(trim($_POST['portal']), FILTER_SANITIZE_STRING);
                $authorizationHeaders = SDKClient::setHeaders($apiParams);
                $client = new SDKClient($apiParams['apiUri'], $authorizationHeaders);

                if (!$cartId || 'undefined' === $cartId) {
                    //Create Cart and use the new cart ID
                    $gql = 'mutation createCart {
                      cart {
                        createCart(
                          input: {
                            currencyCode: "' . ADMWPP_VOUCHER_CURRENCY_CODE .'"
                          }
                        ) {
                          errors {
                            message
                          }
                          cart {
                            id
                          }
                        }
                      }
                    }';

                    $results = $client->runRawQuery($gql);
                    $data = $results->getData();
                    $mutationResponse = $data->cart->createCart;
                    if ($mutationResponse->errors) {
                        $errors = $mutationResponse->errors;
                        $response['status'] = "error";
                        $response['message'] = "";
                        foreach ($errors as $value) {
                            $response['message'] .= $value->message . "<br/>";
                        }
                    } else {
                        $cartId = $mutationResponse->cart->id;
                    }
                }

                if ($cartId) {
                    $response['cartId'] = $cartId;
                    // Add Gift Voucher to cart
                    $variables = array(
                        'cartId' => $cartId,
                        'productOptionId' => $productOptionId,
                        'amount' => $amount,
                    );

                    $gql = 'mutation addGiftVoucherLineItem($cartId: ID!, $productOptionId: ID!, $amount: Decimal!) {
                      cart {
                        addGiftVoucherLineItem(input: {cartId: $cartId, productOptionId: $productOptionId, amount: $amount}) {
                          errors {
                            message
                          }
                          cart {
                            items {
                              quantity
                              productOption {
                                name
                              }
                            }
                          }
                        }
                      }
                    }';

                    $results = $client->runRawQuery($gql, false, $variables);
                    $data = $results->getData();
                    $mutationResponse = $data->cart->addGiftVoucherLineItem;

                    if ($mutationResponse->errors) {
                        $errors = $mutationResponse->errors;
                        $response['status'] = "error";
                        $response['message'] = "";
                        foreach ($errors as $value) {
                            $response['message'] .= $value->message . "<br/>";
                        }
                    } else {
                        $response['status'] = "success";
                        $response['message'] = _x('Gift voucher added to the cart...', 'Gift Voucher', 'admwpp');
                        $response['response'] = $mutationResponse;
                    }
                }
            }
            echo json_encode($response);
            die();
        }

        /*
        * Returns Gift Voucher Form
        */
        public static function addGiftVoucherForm($atts)
        {
            extract(
                shortcode_atts(
                    array(
                    'options_id' => '',
                    'currency_symbol' => ADMWPP_VOUCHER_CURRENCY,
                    'title' => _x('Gift voucher', 'Gift Voucher', 'admwpp'),
                    'button_text' => _x('Add Voucher', 'Gift Voucher', 'admwpp'),
                    ),
                    $atts
                )
            );

            $template = self::getTemplatePath('gift-voucher');
            ob_start();
            include $template;
            $html = ob_get_contents();
            ob_end_clean();

            return $html;
        }

        /*
        * Returns User workshops
        */
        public static function myWorkshops($atts)
        {
            extract(
                shortcode_atts(
                    array(
                        'email' => '',
                        'status' => 'active'
                    ),
                    $atts
                )
            );

            // For now we kept the user email empty as this value should be
            // filled by a dependent plugin or on the theme level using the
            // Filter: admwpp_user_email_workshops used to override the Learner Email.
            $email = apply_filters('admwpp_user_email_workshops', $email);
            // Filter: admwpp_user_status_workshops used to override the Events Learners status.
            $status = apply_filters('admwpp_user_status_workshops', $status);

            $workshops = self::getUserWorkshops($email, $status);
            $template = self::getTemplatePath('my-workshops');
            ob_start();
            include $template;
            $html = ob_get_contents();
            ob_end_clean();

            return $html;
        }

        /**
         * Method to fetch workshops by user email
         * @param  string $email User Email
         * @return array         array of workshops
         */
        public static function getUserWorkshops($email, $status = "active")
        {
            if ($email) {
                $gql = 'query contacts($email: String!, $status: String!) {
                  contacts(filters: [{field: emailAddress, operation: eq, value: $email}]) {
                    edges {
                      node {
                        learners(filters:[{field: lifecycleState, operation:eq, value: $status}]) {
                          edges {
                            node {
                              id
                              event {
                                id
                                code
                                title
                                bookedPlaces
                                start
                                reserved
                                type
                                location {
                                  id
                                  name
                                }
                                courseTemplate {
                                  id
                                  code
                                  title
                                }
                              }
                            }
                          }
                        }
                        personalName {
                          firstName
                          lastName
                        }
                      }
                    }
                  }
                }';

                $variables = array(
                    'email' => $email,
                    'status' => $status
                );

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
                $results = $client->runRawQuery($gql, false, $variables);
                $contacts = $results->getData();

                $workshops = array();
                if ($contacts->contacts->edges[0]->node->learners) {
                    $learners = $contacts->contacts->edges[0]->node->learners;
                    foreach ($learners->edges as $key => $learner) {
                        $learner = $learner->node;
                        $workshops[$learner->event->code]['postId'] = Course::checkifExists(
                            $learner->event->courseTemplate->id
                        );
                        $workshops[$learner->event->code]['title'] = $learner->event->courseTemplate->title;
                        $workshops[$learner->event->code]['events'][$learner->id] = array(
                            'id' => $learner->event->id,
                            'title' => $learner->event->title,
                            'bookedPlaces' => $learner->event->bookedPlaces,
                            'reserved' => $learner->event->reserved,
                            'type' => $learner->event->type,
                            'start' => $learner->event->start,
                            'location' => $learner->event->location->name,
                        );
                    }
                }
                return $workshops;
            }

            return array();
        }

        public static function bundledLps($atts)
        {
            $params = shortcode_atts(
                array(
                    'page' => 1,
                    'per_page' => ADMWPP_PER_PAGE,
                    'ajax' => false,
                    'order_by' => 'name',
                    'order_direction' => 'asc',
                    'post_id' => 0
                    ),
                $atts
            );

            global $post;
            if (isset($post)) {
                $params['post_id'] = $post->ID;
            }
            if (isset($_GET['post_id'])) {
                $params['post_id'] = $_GET['post_id'];
            }

            if (filter_var($params['ajax'], FILTER_VALIDATE_BOOLEAN)) {
                $template = self::getTemplatePath('bundled-lps-ajax');
            } else {
                $bundledLps = self::getBundledLps($params);
                if ($bundledLps['hasNextPage']) {
                    $params['page'] = $bundledLps['currentPage'] + 1;
                }
                $template = self::getTemplatePath('bundled-lps');
            }

            $params['data_attr'] = buildDataAttributes($params, 'string');

            // Set showAddToCart flag based on showAddToCart option
            // (from Administrate Weblink2 Shortcodes plugin - admwswp)
            $showAddToCart = get_field('showAddToCart', 'options');

            extract($params);

            ob_start();
            include $template;
            $html = ob_get_contents();
            ob_end_clean();


            return $html;
        }

        public static function getBundledLpsAjax()
        {
            $params = $_GET['params'];
            $bundledLps = self::getBundledLps($params);

            if ($params['page'] == 1) {
                $template = self::getTemplatePath('bundled-lps');
            } else {
                $template = self::getTemplatePath('bundled-lps-rows');
            }

            if ($bundledLps['hasNextPage']) {
                $params['page'] = $bundledLps['currentPage'] + 1;
            }

            $data_attr = buildDataAttributes($params, 'string');

            // Set showAddToCart flag based on showAddToCart option
            // (from Administrate Weblink2 Shortcodes plugin - admwswp)
            $showAddToCart = get_field('showAddToCart', 'options');

            ob_start();
            include $template;
            $html = ob_get_contents();
            ob_end_clean();

            $response = array(
                'bundledLps' => $bundledLps,
                'params' => $params,
                'html' => $html,
            );
            echo json_encode($response);
            wp_die();
        }

        /**
         * Method to fetch BundledLps
         */
        public static function getBundledLps($params)
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
            $order_by = $params['order_by'];
            $order_direction = $params['order_direction'];

            $searchFields = array(
                '__typename',
                '... on LearningPath' => array(
                    'id',
                    'name',
                    'start',
                    'end',
                    'locale' => array(
                        'name'
                    ),
                    'price' => array(
                        'amount',
                        'financialUnit' => array('symbol')
                    ),
                    'learningObjectives' => array(
                        'type' => 'edges',
                        'fields' => array(
                            'id',
                            '__typename',
                            '... on EventObjective' => array(
                                'id',
                                'event' => array(
                                    'id',
                                    'name',
                                    'remainingPlaces',
                                )
                            )
                        )
                    )
                ),
            );

            $args = array(
                'filters' => array(
                    array(
                        'field' => 'isBundle',
                        'operation' => 'eq',
                        'value' => 'true',
                    )
                ),
                'customFieldFilters' => array(),
                'fields' => $searchFields,
                'sorting' => array(
                    'field' => $order_by,
                    'direction' => $order_direction
                ),
                'paging' => array(
                    'page' => $page,
                    'perPage' => $perPage,
                ),
                'returnType' => 'array', //array, obj, json
            );

            $args = apply_filters('admwpp_bundled_lps_shortcode_args', $args);

            $allCatalogue = $SDKCatalogue->loadAll($args);
            $catalogue = $allCatalogue['catalogue'];
            $pageInfo = $catalogue['pageInfo'];

            $bundledLps = array();
            if (!empty($catalogue['edges'])) {
                foreach ($catalogue['edges'] as $lp) {
                    $lp = $lp['node'];
                    $price = '';
                    $symbol = '';
                    $language = '';
                    $events = array();
                    if (isset($lp['price']) && isset($lp['price']['amount'])) {
                        $price = $lp['price']['amount'];
                        if (isset($lp['price']['financialUnit'])) {
                            $symbol = $lp['price']['financialUnit']['symbol'];
                        }
                    }
                    if (isset($lp["learningObjectives"]["edges"]) && !empty($lp["learningObjectives"]["edges"])) {
                        $learningObjectives = $lp["learningObjectives"]["edges"];
                        foreach ($learningObjectives as $objective) {
                            $objectiveNode = $objective['node'];
                            if ($objectiveNode['__typename'] === 'EventObjective') {
                                $events[] = $objectiveNode["event"];
                            }
                        }
                    }
                    if (isset($lp['locale']) && isset($lp['locale']['name'])) {
                        $language = $lp['locale']['name'];
                    }
                    $bundledLps[$lp['id']] = array(
                        'type' => $lp['__typename'],
                        'name' => $lp['name'],
                        'start' => $lp['start'],
                        'end' => $lp['end'],
                        'formattedPrice' => $symbol . $price,
                        'price' => $price,
                        'symbol' => $symbol,
                        'events' => $events,
                        'language' => $language,
                    );
                }
            }

            $response = array(
                'totalRecords' => $pageInfo['totalRecords'],
                'totalNumPages' => (int) ceil($pageInfo['totalRecords'] / $perPage),
                'currentPage' => $page,
                'hasNextPage' => $pageInfo['hasNextPage'],
                'hasPreviousPage' => $pageInfo['hasPreviousPage'],
                'bundledLps' => $bundledLps,
            );

            return $response;
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
            $themeTemplatePath = get_stylesheet_directory() . '/' . ADMWPP_PREFIX .'/shortcode/';

            // Default Plugin Template
            $pluginTemplatePath = ADMWPP_TEMPLATES_DIR . 'shortcode/';

            $template = $template . ".php";

            if (file_exists($themeTemplatePath . $template)) {
                return $themeTemplatePath . $template;
            }

            return $pluginTemplatePath . $template;
        }
    }
}
