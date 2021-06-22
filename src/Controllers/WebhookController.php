<?php
namespace ADM\WPPlugin\Controllers;

use ADM\WPPlugin\Base;
use ADM\WPPlugin\Oauth2;
use ADM\WPPlugin\Settings;
use ADM\WPPlugin\PostTypes\Course;

if (file_exists('../../../../../wp-load.php')) {
    require_once('../../../../../wp-load.php');
}

class WebhookController extends Base\ActionController
{
    public static function callback($request)
    {
        $jsonBody = $request->get_body();
        $import = array();
        if ($jsonBody) {
            $body = json_decode($jsonBody);
            $responceInstance = $body->metadata->instance;
            $instance = Settings::instance()->getSettingsOption('account', 'instance');
            if ($responceInstance === $instance) {
                if (isset($body->payload->courseTemplates->edges[0])) {
                    $node = $body->payload->courseTemplates->edges[0]->node;
                    $node = json_decode(json_encode($node), true);
                    $import = Course::nodeToPost($node, 'COURSE');
                }
                if (isset($body->payload->events->edges[0])) {
                    $node = $body->payload->events->edges[0]->node->courseTemplate;
                    $node = json_decode(json_encode($node), true);
                    $import = Course::nodeToPost($node, 'COURSE');
                }
                if (isset($body->payload->learningPaths->edges[0])) {
                    $node = $body->payload->learningPaths->edges[0]->node;
                    $node = json_decode(json_encode($node), true);
                    $import = Course::nodeToPost($node, 'LP');
                }
            }
        }
        echo json_encode($import);
        die();
    }
}
