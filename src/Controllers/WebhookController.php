<?php
namespace ADM\WPPlugin\Controllers;

use ADM\WPPlugin\Base;
use ADM\WPPlugin\Oauth2;
use ADM\WPPlugin\Settings;
use ADM\WPPlugin\PostTypes\Course;

if (file_exists(ABSPATH . 'wp-load.php')) {
    require_once(ABSPATH . 'wp-load.php');
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
                // if delete webhook call back check echo
                if (isset($body->payload->echo)) {
                    $nodeId = $body->payload->echo;
                    $nodeIdDecoded = base64_decode($nodeId);
                    $nodeIdDecoded = explode(":", $nodeIdDecoded);
                    if ($nodeIdDecoded[0] === 'Course') { // for an Event
                        $postIds = Course::getPostIdsByEventId($nodeId);
                        foreach ($postIds as $postId) {
                            $nodeId = get_post_meta($postId, 'admwpp_tms_id', true);
                            $nodeType = get_post_meta($postId, 'admwpp_tms_type', true);
                            $node = Course::getNodeById($nodeId, $nodeType);
                            $import = Course::nodeToPost($node, $nodeType);
                        }
                    } else {
                        Course::deleteCourseByNodeId($nodeId);
                    }
                }
                if (isset($body->payload->courseTemplates->edges[0])) {
                    $nodeId = $body->payload->courseTemplates->edges[0]->node->id;
                    $node = Course::getNodeById($nodeId, 'COURSE');
                    $import = Course::nodeToPost($node, 'COURSE');
                }
                if (isset($body->payload->events->edges[0])) {
                    $nodeId = $body->payload->events->edges[0]->node->courseTemplate->id;
                    $node = Course::getNodeById($nodeId, 'COURSE');
                    $import = Course::nodeToPost($node, 'COURSE');
                }
                if (isset($body->payload->learningPaths->edges[0])) {
                    $nodeId = $body->payload->learningPaths->edges[0]->node->id;
                    $node = Course::getNodeById($nodeId, 'LP');
                    $import = Course::nodeToPost($node, 'LP');
                }
                if (isset($body->payload->documents->edges[0])) {
                    $documentNode = $body->payload->documents->edges[0]->node;
                    $courseTemplates = $documentNode->courseTemplates->edges;
                    $learningPaths = $documentNode->learningPaths->edges;
                    if (!empty($courseTemplates)) {
                        foreach ($courseTemplates as $course) {
                            $node = Course::getNodeById($course->node->id, 'COURSE');
                            $import[$course->node->id] = Course::nodeToPost($node, 'COURSE');
                        }
                    }
                    if (!empty($learningPaths)) {
                        foreach ($learningPaths as $learningPath) {
                            $node = Course::getNodeById($learningPath->node->id, 'LP');
                            $import[$learningPath->node->id] = Course::nodeToPost($node, 'LP');
                        }
                    }
                }
            }
        }
        echo json_encode($import);
        die();
    }
}
