<?php
namespace ADM\WPPlugin\Webhooks;

use ADM\WPPlugin\Main;
use ADM\WPPlugin\Settings;
use ADM\WPPlugin\Oauth2;
use ADM\WPPlugin\PostTypes\Course;

use Administrate\PhpSdk\GraphQl\Client as SDKClient;
use Administrate\PhpSdk\GraphQl\QueryBuilder as SDKQueryBuilder;

if (! class_exists('Webhook')) {

    /**
    * Setup the class to handle Webhooks
    *
    * @package default
    * */
    class Webhook
    {
        protected static $instance;

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

        public static function createSynchWebhooks()
        {

            if (is_admin()
                    && isset($_GET['page'])
                    && isset($_GET['tab'])
                    && $_GET['tab'] === 'admwpp_advanced_settings') {
                $settings = get_option('admwpp_advanced_settings');

                if (isset($settings['courses_webhook_type_id'])
                    && !empty($settings['courses_webhook_type_id'])
                ) {
                    //Check if saved already before creating a webhook
                    if (!isset($settings['courses_webhook_id'])
                        || empty($settings['courses_webhook_id'])
                    ) {
                        self::createSynchWebhook($settings['courses_webhook_type_id']);
                    }
                }
                if (isset($settings['lp_webhook_type_id'])
                    && !empty($settings['lp_webhook_type_id'])
                ) {
                    //Check if saved already before creating a webhook
                    if (!isset($settings['lp_webhook_id'])
                        || empty($settings['lp_webhook_id'])
                    ) {
                        self::createSynchWebhook($settings['lp_webhook_type_id'], 'LP');
                    }
                }
                if (isset($settings['event_webhook_type_id'])
                    && !empty($settings['event_webhook_type_id'])
                ) {
                    //Check if saved already before creating a webhook
                    if (!isset($settings['event_webhook_id'])
                        || empty($settings['event_webhook_id'])
                    ) {
                        self::createSynchWebhook($settings['event_webhook_type_id'], 'EVENT');
                    }
                }
            }
        }

        public static function createSynchWebhook($webhookTypeId, $type = 'COURSE')
        {
            $callbakUrl = ADMWPP_SITE_URL . "/wp-json/admwpp/webhook/callback";

            switch ($type) {
                case 'LP':
                    $queryName = 'learningpath';
                    $queryObjects = 'learningPaths';
                    $webhookName = "Wordpress Trigger learning Paths Template Updated";
                    $node = SDKQueryBuilder::buildNode(Course::$learningPathFields);
                    $webhookIdOptionsKey = 'lp_webhook_id';
                    break;
                case 'EVENT':
                    $queryName = 'events';
                    $queryObjects = 'events';
                    $webhookName = "Wordpress Trigger Events Updated";

                    $courseFields = Course::$courseFields;
                    $eventFields = array(
                        'id',
                        'courseTemplate' => $courseFields
                    );

                    $node = SDKQueryBuilder::buildNode($eventFields);
                    $webhookIdOptionsKey = 'event_webhook_id';
                    break;
                default:
                    $queryName = 'courses';
                    $queryObjects = 'courseTemplates';
                    $webhookName = "Wordpress Trigger Course Template Updated";
                    $node = SDKQueryBuilder::buildNode(Course::$courseFields);
                    $webhookIdOptionsKey = 'courses_webhook_id';
                    break;
            }

            $edges = (new SDKQueryBuilder('edges'))->selectField($node);

            $edgesQuery = $edges->getQuery();
            $edgesQuery = str_replace("query ", "", $edgesQuery);
            $edgesQuery = str_replace(array("\n", "\r"), ' ', $edgesQuery);
            $edgesQuery = trim($edgesQuery);

            $query = 'query ' . $queryName . ' ($objectid: String!) {' . $queryObjects . ' (filters: [{field: id, operation: eq, value: $objectid}]) ' . $edgesQuery . ' }';

            $variables = array(
                'name' => $webhookName,
                'webhookTypeId' => $webhookTypeId,
                'callbakUrl' => $callbakUrl,
                'query' => $query,
                'email' => "jck@administrate.co"
            );

            $gql = 'mutation createWebhook($name: String!, $email: String!, $webhookTypeId: ID!, $callbakUrl: String!, $query: String!) {
              webhooks {
                create(input: {
                  name: $name,
                  emailAddress: $email,
                  webhookTypeId: $webhookTypeId,
                  url: $callbakUrl,
                  query: $query}
                ) {
                  webhook {
                    id
                  }
                  errors {
                    message
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
            $results = $client->runRawQuery($gql, true, $variables);
            $webhook = $results->getData();

            $webhookId = '';
            if (isset($webhook['webhooks']['create']['webhook'])) {
                $webhookId = $webhook['webhooks']['create']['webhook']['id'];
                Settings::instance()->setSettingsOption('advanced', $webhookIdOptionsKey, $webhookId);
            } else {
                if (isset($webhook['webhooks']['create']['errors'])) {
                    $message = __('Webhooks Creation Failed: ', ADMWPP_TEXT_DOMAIN);
                    $message .= $webhook['webhooks']['create']['errors'][0]['message'];

                    $flash = Main::instance()->getFlash();
                    $flash->setMessage(array(
                      'message' => $message,
                      'success' => 'error',
                    ));
                }
            }

            // Activate webhook after creation
            if ($webhookId) {
                $variables = array(
                    'webhookId' => $webhookId
                );

                $gql = 'mutation updateWebhookConfig($webhookId: ID!) {
                  webhooks {
                    update(
                      input: {
                        webhookId: $webhookId,
                        lifecycleState: active
                        }
                    ) {
                      errors {
                        message
                      }
                      webhook {
                        id
                      }
                    }
                  }
                }';

                $client = new SDKClient($apiParams['apiUri'], $authorizationHeaders);
                $results = $client->runRawQuery($gql, true, $variables);
                $webhook = $results->getData();

                if (isset($webhook['webhooks']['update'])) {
                    $webhookId = $webhook['webhooks']['update']['webhook']['id'];
                    Settings::instance()->setSettingsOption('advanced', $webhookIdOptionsKey, $webhookId);

                    $flash = Main::instance()->getFlash();
                    $flash->setMessage(array(
                        'message' => 'Webhooks successfully created.',
                        'success' => 'success',
                    ));
                }
            }
        }
    }
}
