<?php
namespace ADM\WPPlugin\Controllers;

use ADM\WPPlugin\Base;
use ADM\WPPlugin\Oauth2;
use ADM\WPPlugin\Settings;
use ADM\WPPlugin\Taxonomies\LearningCategory;

use Administrate\PhpSdk\Category;

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

        $activate = Oauth2\Activate::instance();
        $apiParams = $activate::$params;

        $accessToken = $activate->getAuthorizeToken()['token'];
        $appId = Settings::instance()->getSettingsOption('account', 'app_id');
        $instance = Settings::instance()->getSettingsOption('account', 'instance');

        $apiParams['accessToken'] = $accessToken;
        $apiParams['clientId'] = $appId;
        $apiParams['instance'] = $instance;

        $categories = new Category($apiParams);

        $args = array(
            'paging' => array(
                'page' => (int) $params['page'],
                'perPage' => (int) $params['per_page']
            ),
            'sorting' => array(
                'field' => 'id',
                'direction' => 'asc'
            ),
            'fields' => array(
                'id',
                'legacyId',
                'name',
                'description',
                'parentCategory' => array(
                    'id',
                    'legacyId',
                    'name',
                    'description'
                ),
            ),
            'returnType' => 'array', //array, obj, json
            'coreApi' => true,
        );

        $allCategories = $categories->loadAll($args);

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

            $name = $node['name'];
            $description = $node['description'];
            $parentCategory = $node['parentCategory'];

            $taxonomy = LearningCategory::$system_name;

            $termArgs = array(
                'description' => $description,
                'slug' => sanitize_title($name),
            );

            if (!empty($parentCategory)) {
                $parentName = $parentCategory['name'];
                $parentSlug = sanitize_title($parentName);
                $parentTerm = get_term_by('slug', $parentSlug, $taxonomy);
                $termArgs['parent'] = $parentTerm->term_id;
                $termArgs['slug'] .= "-" . sanitize_title($parentName);
            }

            $term = wp_insert_term(
                $name,
                $taxonomy,
                $termArgs
            );

            if (is_wp_error($term)) {
                $termId = $term->get_error_data('term_exists');
                $results['exists']++;
            } else {
                $termId = $term['term_id'];
                $results['imported']++;

                $metas = LearningCategory::$metas;
                foreach ($metas as $key => $value) {
                    $tmsKey = $value['tmsKey'];
                    update_term_meta($termId, $key, $node[$tmsKey]);
                }
            }
        }

        $results['message'] = 'Total: ' . $results['totalRecords'];
        $results['message'] .= '<br/>Imported: ' . $results['imported'];
        $results['message'] .= '<br/>Exists: ' . $results['exists'];

        if ($results['hasNextPage'] == true) {
            $next = (int) $params['page'] + 1;
            $results['message'] .= '<br/>Next Page: ' . $next;
        }

        echo json_encode($results);
        die();
    }
}
