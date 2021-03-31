<?php
namespace ADM\WPPlugin\Shortcodes;

use ADM\WPPlugin as ADMWPP;
use ADM\WPPlugin\Oauth2;
use ADM\WPPlugin\Settings;
use ADM\WPPlugin\PostTypes\Course;

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
        }

        /**
         * [addShortcodes description]
         */
        protected function addShortcodes()
        {
            add_shortcode('admwpp-gift-voucher', array($this, 'addGiftVoucherForm'));
            add_shortcode('admwpp-my-workshops', array($this, 'myWorkshops'));
        }

        /*
        * Process Add Gift Voucher Form
        */
        public static function addGiftVoucher()
        {
            $response = array(
                'status' => 'error',
                'message' => __('Something went Wrong Try again please.', 'admwpp'),
                'response' => '',
                'cartUrl' => home_url('/cart/'),
            );
            if (isset($_POST['productOptionId']) && isset($_POST['amount']) && isset($_POST['portalToken'])) {
                $cartId = $_POST['cartId'];
                $productOptionId = $_POST['productOptionId'];
                $amount = (float) $_POST['amount'];

                $activate = Oauth2\Activate::instance();
                $activate->setParams(true);
                $apiParams = $activate::$params;
                $apiParams['portalToken'] = $_POST['portalToken'];
                $apiParams['portal'] = $_POST['portal'];
                $authorizationHeaders = SDKClient::setHeaders($apiParams);
                $client = new SDKClient($apiParams['apiUri'], $authorizationHeaders);

                if (!$cartId || 'undefined' === $cartId) {
                    //Create Cart and use the new cart ID
                    $gql = 'mutation createCart {
                      cart {
                        createCart(
                          input: {
                            currencyCode: "EUR"
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
                        $response['message'] = __('Gift voucher added to the cart...', 'admwpp');
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
                    'currency_symbol' => '€',
                    'title' => __('Gift voucher', 'admwpp'),
                    'button_text' => __('Add Voucher', 'admwpp'),
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
                    ),
                    $atts
                )
            );

            // For now we kept the user email empty as this value should be
            // filled by a dependent plugin or on the theme level using the
            // filter: admwpp_user_email_workshops.
            $email = apply_filters('admwpp_user_email_workshops', $email);

            $workshops = self::getUserWorkshops($email);
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
        public static function getUserWorkshops($email)
        {
            if ($email) {
                $gql = 'query contacts {
                  contacts(filters: [{field: emailAddress, operation: eq, value: "' . $email . '"}]) {
                    edges {
                      node {
                        learners {
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
