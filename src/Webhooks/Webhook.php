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

        public static function getClient()
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
            return $client;
        }

        public static function createSynchWebhooks()
        {
            if (is_admin()
                    && isset($_GET['page'])
                    && isset($_GET['tab'])
                    && $_GET['tab'] === 'admwpp_advanced_settings'
                    && isset($_GET['settings-updated'])) {
                $settings = get_option('admwpp_advanced_settings');

                if (isset($settings['courses_webhook_type_id'])
                    && !empty($settings['courses_webhook_type_id'])
                ) {
                    //Check if saved already before creating a webhook
                    if (!isset($settings['courses_webhook_id'])
                        || empty($settings['courses_webhook_id'])
                    ) {
                        self::createSynchWebhook($settings['courses_webhook_type_id']);
                    } else {
                        self::updateSynchWebhook($settings['courses_webhook_id']);
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
                    } else {
                        self::updateSynchWebhook($settings['lp_webhook_id'], 'LP');
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
                    } else {
                        self::updateSynchWebhook($settings['event_webhook_id'], 'EVENT');
                    }
                }
                if (isset($settings['document_webhook_type_id'])
                    && !empty($settings['document_webhook_type_id'])
                ) {
                    //Check if saved already before creating a webhook
                    if (!isset($settings['document_webhook_id'])
                        || empty($settings['document_webhook_id'])
                    ) {
                        self::createSynchWebhook($settings['document_webhook_type_id'], 'DOCUMENT');
                    } else {
                        self::updateSynchWebhook($settings['document_webhook_id'], 'DOCUMENT');
                    }
                }

                if (isset($settings['courses_delete_webhook_type_id'])
                    && !empty($settings['courses_delete_webhook_type_id'])
                ) {
                    //Check if saved already before creating a webhook
                    if (!isset($settings['courses_delete_webhook_id'])
                        || empty($settings['courses_delete_webhook_id'])
                    ) {
                        self::createSynchWebhook($settings['courses_delete_webhook_type_id'], 'COURSE', 'delete');
                    } else {
                        self::updateSynchWebhook($settings['courses_delete_webhook_id'], 'COURSE', 'delete');
                    }
                }

                if (isset($settings['lp_delete_webhook_type_id'])
                    && !empty($settings['lp_delete_webhook_type_id'])
                ) {
                    //Check if saved already before creating a webhook
                    if (!isset($settings['lp_delete_webhook_id'])
                        || empty($settings['lp_delete_webhook_id'])
                    ) {
                        self::createSynchWebhook($settings['lp_delete_webhook_type_id'], 'LP', 'delete');
                    } else {
                        self::updateSynchWebhook($settings['lp_delete_webhook_id'], 'LP', 'delete');
                    }
                }

                if (isset($settings['event_delete_webhook_type_id'])
                    && !empty($settings['event_delete_webhook_type_id'])
                ) {
                    //Check if saved already before creating a webhook
                    if (!isset($settings['event_delete_webhook_id'])
                        || empty($settings['event_delete_webhook_id'])
                    ) {
                        self::createSynchWebhook($settings['event_delete_webhook_type_id'], 'EVENT', 'delete');
                    } else {
                        self::updateSynchWebhook($settings['event_delete_webhook_id'], 'EVENT', 'delete');
                    }
                }
            }
        }

        public static function buildCreateWebhooKInput($webhookTypeId = '', $type = 'COURSE', $webhookId = 0)
        {
            switch ($type) {
                case 'LP':
                    $webhookName = "Wordpress Trigger learning Paths Template Updated";
                    $queryName = 'learningpath';
                    $queryObjects = 'learningPaths';
                    $learningPathFields = array(
                        'id'
                    );
                    $buildNode = SDKQueryBuilder::buildNode($learningPathFields);
                    $node = $buildNode['node'];
                    break;
                case 'EVENT':
                    $webhookName = "Wordpress Trigger Events Updated";
                    $queryName = 'events';
                    $queryObjects = 'events';
                    $eventFields = array(
                        'id',
                        'courseTemplate' => array(
                            'id'
                        )
                    );
                    $buildNode = SDKQueryBuilder::buildNode($eventFields);
                    $node = $buildNode['node'];
                    break;
                case 'DOCUMENT':
                    $webhookName = "Wordpress Trigger Document Updated";
                    $queryName = 'documents';
                    $queryObjects = 'documents';
                    $documentFields = array(
                        'id',
                        'courseTemplates' => array(
                            'type' => 'edges',
                            'fields' => array(
                                'id',
                            )
                        ),
                        'learningPaths' => array(
                            'type' => 'edges',
                            'fields' => array(
                                'id',
                            )
                        )
                    );
                    $buildNode = SDKQueryBuilder::buildNode($documentFields);
                    $node = $buildNode['node'];
                    break;
                default:
                    $webhookName = "Wordpress Trigger Course Template Updated";
                    $queryName = 'courses';
                    $queryObjects = 'courseTemplates';
                    $courseFields = array(
                        'id'
                    );
                    $buildNode = SDKQueryBuilder::buildNode($courseFields);
                    $node = $buildNode['node'];
                    break;
            }

            $edges = (new SDKQueryBuilder('edges'))->selectField($node);

            $edgesQuery = $edges->getQuery();
            $edgesQuery = str_replace("query ", "", $edgesQuery);
            $edgesQuery = str_replace(array("\n", "\r"), ' ', $edgesQuery);
            $edgesQuery = trim($edgesQuery);

            $query = 'query ' . $queryName . ' ($objectid: String!) {' . $queryObjects . ' (filters: [{field: id, operation: eq, value: $objectid}]) ' . $edgesQuery . ' }';

            $input = array(
                'name' => $webhookName,
                'url' => ADMWPP_WEBHOOK_CALLBACK,
                'query' => $query,
                'emailAddress' => get_bloginfo('admin_email')
            );

            if ($webhookTypeId) {
                $input['webhookTypeId'] = $webhookTypeId;
            }

            if ($webhookId) {
                $input['webhookId'] = $webhookId;
            }

            return $input;
        }

        public static function createSynchWebhook($webhookTypeId, $type = 'COURSE', $action = 'synch')
        {
            if ($action === 'delete') {
                switch ($type) {
                    case 'LP':
                        $webhookIdOptionsKey = 'lp_delete_webhook_id';
                        break;
                    case 'EVENT':
                        $webhookIdOptionsKey = 'event_delete_webhook_id';
                        break;
                    default:
                        $webhookIdOptionsKey = 'courses_delete_webhook_id';
                        break;
                }
                $input = self::buildDeleteWebhooKInput($webhookTypeId, $type);
            } else {
                switch ($type) {
                    case 'LP':
                        $webhookIdOptionsKey = 'lp_webhook_id';
                        break;
                    case 'EVENT':
                        $webhookIdOptionsKey = 'event_webhook_id';
                        break;
                    case 'DOCUMENT':
                        $webhookIdOptionsKey = 'document_webhook_id';
                        break;
                    default:
                        $webhookIdOptionsKey = 'courses_webhook_id';
                        break;
                }
                $input = self::buildCreateWebhooKInput($webhookTypeId, $type);
            }

            $variables = array('input' => $input);

            $gql = 'mutation createWebhook($input: WebhookCreateInput!) {
              webhooks {
                create(input: $input) {
                  webhook {
                    id
                  }
                  errors {
                    message
                  }
                }
              }
            }';

            $client = self::getClient();
            $results = $client->runRawQuery($gql, true, $variables);
            $webhook = $results->getData();

            $webhookId = '';
            if (isset($webhook['webhooks']['create']['webhook'])) {
                $webhookId = $webhook['webhooks']['create']['webhook']['id'];
                Settings::instance()->setSettingsOption('advanced', $webhookIdOptionsKey, $webhookId);
                $messageArgs = array(
                    'message' =>  __('Webhook successfully Created.', ADMWPP_TEXT_DOMAIN),
                );
            } else {
                $message = __('Webhook Creation Failed: ', ADMWPP_TEXT_DOMAIN);
                if (isset($webhook['webhooks']['create']['errors'])) {
                    $message .= $webhook['webhooks']['create']['errors'][0]['message'];
                }
                $messageArgs = array(
                    'message' =>  $message,
                    'success' => 'error',
                );
            }

            $flash = Main::instance()->getFlash();
            $flash->setMessage($messageArgs);

            // Activate webhook after creation
            if ($webhookId) {
                $webhookId = self::activateSynchWebhook($webhookId, $type, $action, $client);
                if ($webhookId) {
                    Settings::instance()->setSettingsOption('advanced', $webhookIdOptionsKey, $webhookId);
                }
            }
        }

        public static function activateSynchWebhook($webhookId, $type, $action, $client)
        {
            if (!isset($client)) {
                $client = self::getClient();
            }
            $input = array(
                'webhookId' => $webhookId,
                'lifecycleState'=> 'active'
            );
            $webhookId = self::updateSynchWebhook($webhookId, $type, $action, $input, $client);
            if ($webhookId) {
                $messageArgs = array(
                    'message' =>  __('Webhook successfully Activated.', ADMWPP_TEXT_DOMAIN),
                );
            } else {
                $webhookId = "";
                $message = __('Webhook Activation Failed: ', ADMWPP_TEXT_DOMAIN);
                if (isset($webhook['webhooks']['create']['errors'])) {
                    $message .= $webhook['webhooks']['create']['errors'][0]['message'];
                }
                $messageArgs = array(
                    'message' =>  $message,
                    'success' => 'error',
                );
            }

            $flash = Main::instance()->getFlash();
            $flash->setMessage($messageArgs);

            return $webhookId;
        }

        public static function updateSynchWebhook($webhookId, $type = 'COURSE', $action = 'synch', $input = array(), $client = null)
        {
            if (!$client) {
                $client = self::getClient();
            }

            if (empty($input)) {
                if ($action === 'delete') {
                    $input = self::buildDeleteWebhooKInput('', $type, $webhookId);
                } else {
                    $input = self::buildCreateWebhooKInput('', $type, $webhookId);
                }
            }

            $variables = array('input' => $input);

            $gql = 'mutation updateWebhookConfig($input: WebhookUpdateInput!) {
              webhooks {
                update(input: $input) {
                  errors {
                    message
                  }
                  webhook {
                    id
                  }
                }
              }
            }';

            $results = $client->runRawQuery($gql, true, $variables);
            $webhook = $results->getData();

            if (isset($webhook['webhooks']['update'])) {
                if (isset($webhook['webhooks']['update']['webhook']['id'])) {
                    $webhookId = $webhook['webhooks']['update']['webhook']['id'];
                    $messageArgs = array(
                        'message' =>  __('Webhook successfully Updated.', ADMWPP_TEXT_DOMAIN),
                    );
                } else {
                    $webhookId = '';
                    $message = __('Webhook Update Failed: ', ADMWPP_TEXT_DOMAIN);
                    if (isset($webhook['webhooks']['update']['errors'])) {
                        $message .= $webhook['webhooks']['update']['errors'][0]['message'];
                    }
                    $messageArgs = array(
                        'message' =>  $message,
                        'success' => 'error',
                    );
                }

                $flash = Main::instance()->getFlash();
                $flash->setMessage($messageArgs);

                return $webhookId;
            }
            return '';
        }

        public static function buildDeleteWebhooKInput($webhookTypeId = '', $type = 'COURSE', $webhookId = 0)
        {
            switch ($type) {
                case 'LP':
                    $webhookName = "Wordpress Trigger learning Paths Template Delete";
                    $queryName = 'learningpath';
                    break;
                case 'EVENT':
                    $webhookName = "Wordpress Trigger Events Delete";
                    $queryName = 'events';
                    break;
                default:
                    $webhookName = "Wordpress Trigger Course Template Delete";
                    $queryName = 'courses';
                    break;
            }

            $query = 'query ' . $queryName . ' ($objectid: String!) {echo(value: $objectid)}';

            $input = array(
                'name' => $webhookName,
                'url' => ADMWPP_WEBHOOK_CALLBACK,
                'query' => $query,
                'emailAddress' => get_bloginfo('admin_email')
            );

            if ($webhookTypeId) {
                $input['webhookTypeId'] = $webhookTypeId;
            }

            if ($webhookId) {
                $input['webhookId'] = $webhookId;
            }

            return $input;
        }
    }
}
