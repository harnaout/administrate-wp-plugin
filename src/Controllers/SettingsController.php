<?php
namespace ADM\WPPlugin\Controllers;

use ADM\WPPlugin\Base;
use ADM\WPPlugin\Oauth2;
use ADM\WPPlugin\Settings;
use ADM\WPPlugin\Taxonomies\LearningCategory;
use ADM\WPPlugin\PostTypes\Course;

if (file_exists('../../../../../wp-load.php')) {
    require_once('../../../../../wp-load.php');
}

class SettingsController extends Base\ActionController
{
    public static function reset()
    {
        $params = self::$params;
        $id     = $params['id'];
        delete_option($id);

        self::setFlash(__('Settings successfully reseted.', ADMWPP_TEXT_DOMAIN));
        wp_redirect(admin_url('/admin.php?page=admwpp-settings&tab=' . $id));
        exit;
    }

    public static function importLearningCategories()
    {
        $params = self::$params;

        $allCategories = LearningCategory::getCategories($params);

        $learningCategories = $allCategories['learningCategories'];
        $pageInfo = $learningCategories['pageInfo'];
        $learningCategories = $learningCategories['edges'];

        $results = array(
            'totalRecords' => $pageInfo['totalRecords'],
            'hasNextPage' => $pageInfo['hasNextPage'],
            'hasPreviousPage' => $pageInfo['hasPreviousPage'],
            'message' => '',
            'imported' => (int) $params['imported'],
            'exists' => (int) $params['exists'],
        );

        if (empty($learningCategories)) {
            $results['message'] = 'No Learning Categories found...';
            echo json_encode($results);
            die();
        }

        foreach ($learningCategories as $node) {
            $node = $node['node'];

            $import = LearningCategory::nodeToTerm($node);

            $results['imported'] += $import['imported'];
            $results['exists'] += $import['exists'];

            $results[$node['id']] = $import;
        }

        $results['message'] = 'Total: ' . $results['totalRecords'];
        $results['message'] .= '<br/>Imported: ' . $results['imported'];
        $results['message'] .= '<br/>Exists: ' . $results['exists'];

        if ($results['hasNextPage'] == true) {
            $next = (int) $params['page'] + 1;
            $results['message'] .= '<br/>Next Page: ' . $next;
        }

        if (($results['imported'] + $results['exists']) == $results['totalRecords']) {
            self::setFlash(__('Successfully Imported Categories.', ADMWPP_TEXT_DOMAIN));
        }

        echo json_encode($results);
        die();
    }

    public static function importCourses()
    {
        $params = self::$params;

        $allCourses = Course::getCourses($params);

        $courseTemplates = $allCourses['courseTemplates'];
        $pageInfo = $courseTemplates['pageInfo'];
        $courseTemplates = $courseTemplates['edges'];

        $results = array(
            'totalRecords' => $pageInfo['totalRecords'],
            'hasNextPage' => $pageInfo['hasNextPage'],
            'hasPreviousPage' => $pageInfo['hasPreviousPage'],
            'message' => '',
            'imported' => (int) $params['imported'],
            'exists' => (int) $params['exists'],
        );

        if (empty($courseTemplates)) {
            $results['message'] = 'No Courses found...';
            echo json_encode($results);
            die();
        }

        foreach ($courseTemplates as $node) {
            $node = $node['node'];

            $import = Course::nodeToPost($node);

            $results['imported'] += $import['imported'];
            $results['exists'] += $import['exists'];

            $results[$node['id']] = $import;
        }

        $results['message'] = 'Total: ' . $results['totalRecords'];
        $results['message'] .= '<br/>Imported: ' . $results['imported'];
        $results['message'] .= '<br/>Exists: ' . $results['exists'];

        if ($results['hasNextPage'] == true) {
            $next = (int) $params['page'] + 1;
            $results['message'] .= '<br/>Next Page: ' . $next;
        }

        if (($results['imported'] + $results['exists']) == $results['totalRecords']) {
            self::setFlash(__('Successfully Imported Courses.', ADMWPP_TEXT_DOMAIN));
        }

        echo json_encode($results);
        die();
    }
}
