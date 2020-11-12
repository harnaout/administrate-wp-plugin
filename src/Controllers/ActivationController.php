<?php
namespace ADM\WPPlugin\Controllers;

use ADM\WPPlugin as ADMWPP;
use ADM\WPPlugin\Base;
use ADM\WPPlugin\Oauth2;
use ADM\WPPlugin\Settings;

if (file_exists('../../../../../wp-load.php')) {
    require_once('../../../../../wp-load.php');
}

class ActivationController extends Base\ActionController
{

    static $redirect_url;
    static $oauth_server;
    static $activation_url = '/admin.php?page=admwpp-settings';

    public static function authorize()
    {

        update_option('admwpp_active', 0);

        $params = self::$params;

        $instance = $params['admwpp_account_settings']['instance'];
        $app_id = $params['admwpp_account_settings']['app_id'];
        $app_secret = $params['admwpp_account_settings']['app_secret'];

        Settings::instance()->setSettingsOption('account', 'instance', $instance);
        Settings::instance()->setSettingsOption('account', 'app_id', $app_id);
        Settings::instance()->setSettingsOption('account', 'app_secret', $app_secret);

        $activate = Oauth2\Activate::instance();

        $redirect_url = $activate->getAuthorizeUrl();

        if (! empty($redirect_url)) {
            wp_redirect($redirect_url);
        } else {
            if (self::formatIsJson()) {
                $response = array(
                    'status' => 'error',
                    'message' => 'App ID and Secret cannot be empty.',
                );
                echo json_encode($response);
                exit;
            } else {
                self::setFlash('Instance, App ID and Secret cannot be empty.', false);
                wp_redirect(admin_url(self::$activation_url));
                exit;
            }
        }
    }

    public static function callback()
    {
        if (self::$params) {
            $params = self::$params;
        } else {
            $params = $_GET;
        }

        $activate = Oauth2\Activate::instance();

        if ($activate->authorize($params)) {
            update_option('admwpp_active', 1);

            ADMWPP\Main::instance()->loadActiveFeatures();

            if (self::formatIsJson()) {
                $response = array(
                    'status' => 'success',
                    'message' => 'Application successfully authenticated.',
                );
                echo json_encode($response);
                exit;
            } else {
                self::setFlash('Application successfully authenticated.');
                wp_redirect(admin_url(self::$activation_url));
                exit;
            }
        } else {
            if (self::formatIsJson()) {
                $response = array(
                    'status' => 'error',
                    'message' => 'Could not authenticate application.',
                );
                echo json_encode($response);
                exit;
            } else {
                self::setFlash('Could not authenticate application.', false);
                wp_redirect(admin_url(self::$activation_url));
                exit;
            }
        }
    }
}
