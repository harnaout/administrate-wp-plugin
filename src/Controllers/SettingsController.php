<?php
namespace ADM\WPPlugin\Controllers;

use ADM\WPPlugin\Base as Base;

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

        self::set_flash(__('Settings successfully reseted.', ADMWPP_TEXT_DOMAIN));
        wp_redirect(admin_url('/admin.php?page=admwpp-settings&tab=' . $id));
        exit;
    }
}
