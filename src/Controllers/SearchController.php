<?php
namespace ADM\WPPlugin\Controllers;

use ADM\WPPlugin\Base;
use ADM\WPPlugin\Oauth2;
use ADM\WPPlugin\Api;

if (file_exists('../../../../../wp-load.php')) {
    require_once('../../../../../wp-load.php');
}

class SearchController extends Base\ActionController
{
    public static function partners()
    {
        $params = self::$params;
        $search = $params['query'];
        $accountAssosiations = Api\Search::getAccountAssosiations($search);
        $accounts[] = array(
            'value' => '',
            'label' => __('No Results', ADMWPP_TEXT_DOMAIN))
        ;
        if ($accountAssosiations) {
            $accounts = array();
            foreach ($accountAssosiations as $key => $value) {
                $accounts[] = array(
                    'value' => $key,
                    'label' => $value
                );
            }
        }
        echo json_encode($accounts);
        die();
    }
}
