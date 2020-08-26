<?php
namespace ADM\WPPlugin\Oauth2;

use Administrate\PhpSdk\Oauth\Activator;

use ADM\WPPlugin as ADMWPP;

if (file_exists('../../../../wp-load.php')) {
    require_once('../../../../wp-load.php');
}

if (!class_exists('Activate')) {

    /**
     * This class is responsible for activating the plugin through oAuth
     * with the main Bookwitty Accounts app.
     *
     * @package default
     *
     */
    class Activate
    {
        protected static $instance;
        static $redirect_uri;
        static $oauth_server;
        static $activationObj;
        static $params;

        // Grace Period to refresh token at 90%
        // of Expires IN.
        const TOKEN_GRACE_PERIOD = '0.9';

        // Token Statuses
        const TOKEN_STATUS_NEW      = 'new';
        const TOKEN_STATUS_ERROR    = 'error';
        const TOKEN_STATUS_RENEWING = 'renewing';

        // Token Expiry Statuses
        const TOKEN_EXPIRY_STATUS_EXPIRED       = 'expired';
        const TOKEN_EXPIRY_STATUS_VALID         = 'valid';
        const TOKEN_EXPIRY_STATUS_GRACE_PERIOD  = 'grace_period';

        /**
         * Default constructor.
         * Set the static variables.
         *
         * @return void
         *
         */
        protected function __construct()
        {
            self::setParams();
            self::$activationObj = new Activator();
        }

        /**
         * Static Singleton Factory Method
         * Return an instance of the current class if it exists
         * Construct a new one otherwise
         *
         * @return Activate object
         *
         */
        public static function instance()
        {
            if (!isset(self::$instance)) {
                $className = __CLASS__;
                self::$instance = new $className;
            }
            return self::$instance;
        }

        /**
         * Checks APP Environment and sets the Params accordingly.
         *
         * @return void
         *
         * */
        public function setParams()
        {
            global $ADMWPP_APP_ENVIRONMENT;
            $env = ADMWPP_ENV;

            if (!empty($env)) {
                self::$params = $ADMWPP_APP_ENVIRONMENT[$env];
            } else {
                self::$params = $ADMWPP_APP_ENVIRONMENT['sandbox'];
            }

            self::setRedirectUri();
            self::$params['redirectUri'] = self::$redirect_uri;
        }

        /**
         * Function to build the authorization URL.
         * It fetches the necessary app id and secret from the saved options.
         *
         * @return $request_url, string, the authorization URL.
         *
         * */
        public function getAuthorizeUrl($appId, $appSecret)
        {
            $params = self::$params;
            $params['clientId'] = $appId;
            $params['clientSecret'] = $appSecret;

            self::$activationObj->setParams($params);

            return self::$activationObj->getAuthorizeUrl();
        }


        /**
         * Function to build the API call headers
         * @return $headers, array API Call Header configuration.
         */
        public static function setHeaders()
        {
            $headers = array(
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept'       => 'application/json;',
            );
            return $headers;
        }

        /**
         * Function to build the API call args
         * @return $args, array API Call args configuration.
         */
        public static function setArgs()
        {
            $args = array(
                'timeout'       => 10,
                'redirection'   => 5,
                'httpversion'   => '1.0',
                'blocking'      => true,
                'headers'       => self::setHeaders(),
                'cookies'       => array(),
                'sslverify'     => false,
            );
            return $args;
        }

        /**
         * Function get Authorization token
         *
         * @return $authorize_token, array, the authorization Token Array.
         *
         * */
        public function getAuthorizeToken()
        {
            $access_token = ADMWPP\Settings::instance()->getSettingsOption('account', 'access_token');
            $token_type   = ADMWPP\Settings::instance()->getSettingsOption('account', 'token_type');

            $authorize_token = array(
                'type'  => $token_type,
                'token' => $access_token
            );

            $expiry_status = $this->accessTokenExpired();

            if ($expiry_status === self::TOKEN_EXPIRY_STATUS_GRACE_PERIOD) {
                $token_status = ADMWPP\Settings::instance()->getSettingsOption('account', 'token_status');
                if ($token_status === self::TOKEN_STATUS_RENEWING) {
                    return $authorize_token;
                }
            } elseif ($expiry_status === self::TOKEN_EXPIRY_STATUS_VALID) {
                return $authorize_token;
            }

            if (!$this->renewAuthorizeToken()) {
                return $authorize_token;
            }

            $authorize_token['token'] = ADMWPP\Settings::instance()->getSettingsOption('account', 'access_token');
            $authorize_token['type']  = ADMWPP\Settings::instance()->getSettingsOption('account', 'token_type');

            return $authorize_token;
        }

        /**
         * Function To save new authorization token
         * It fetches the necessary app id and secret from the saved options.
         * */
        public function renewAuthorizeToken()
        {

            ADMWPP\Settings::instance()->setSettingsOption(
                'account',
                'token_status',
                self::TOKEN_STATUS_RENEWING
            );

            $oauth_server = self::$oauth_server ?: self::setOauthServer();

            $app_id     = ADMWPP\Settings::instance()->getSettingsOption('account', 'app_id');
            $app_secret = ADMWPP\Settings::instance()->getSettingsOption('account', 'app_secret');

            if (admwppBlank($app_id) || admwppBlank($app_secret)) {
                return;
            }

            //Request Token
            $url  = $oauth_server . "oauth/token";

            $args = self::setArgs();

            $body = array(
                'grant_type'    => 'client_credentials',
                'client_id'     => $app_id,
                'client_secret' => $app_secret,
                'scope'         => "content orders public customer"
            );

            $args['body'] = json_encode($body);

            $response = wp_remote_post($url, $args);
            return $this->saveAccessToken($response);
        }

        /**
        * Function to get a Save Access Token.
        */
        protected function saveAccessToken($response)
        {
            // If the response gave us an error, return.
            if (is_wp_error($response)) {
                ADMWPP\Settings::instance()->setSettingsOption(
                    'account',
                    'token_status',
                    self::TOKEN_STATUS_ERROR
                );
                return false;
            }

            if ($response['response']['code'] === 200) {
                $result = json_decode($response['body']);

                $access_token  = $result->access_token;
                $token_type    = $result->token_type;
                $expires_in    = $result->expires_in;
                $created_at    = $result->created_at;

                ADMWPP\Settings::instance()->setSettingsOption('account', 'access_token', $access_token);
                ADMWPP\Settings::instance()->setSettingsOption('account', 'token_type', $token_type);
                ADMWPP\Settings::instance()->setSettingsOption('account', 'expires_in', $expires_in);
                ADMWPP\Settings::instance()->setSettingsOption('account', 'created_at', $created_at);

                ADMWPP\Settings::instance()->setSettingsOption(
                    'account',
                    'token_status',
                    self::TOKEN_STATUS_NEW
                );

                return true;
            } else {
                ADMWPP\Settings::instance()->setSettingsOption(
                    'account',
                    'token_status',
                    self::TOKEN_STATUS_ERROR
                );
                return false;
            }
        }


        /**
         * Function to handle authorize callback.
         *
         * Checks for received authorization code,
         * And Sends the code using Post to  authorization server.
         *
         * If success set variables and return true to controller.
         * If Fails return null.
         *
         * */
        public function authorize($params = array())
        {
            // If the callback is the result of an authorization call to
            // the oAuth server:
            //      - Ask for the access token
            //      - Save the access token and all other info
            if (! empty($params['code'])) {
                $code = $params['code'];

                if (! $this->fetchAccessToken($code)) {
                    return false;
                }

                return true;
            } else {
                return false;
            }
        }

        /**
         * Function to make secure oAuth Call.
         *
         * Check if the access token is expired before making a call
         * to the oAuth server.
         * If the access token is expired, fetch another one.
         * To fetch a new access token, we must send the authorize API
         * on the server the refresh token and get a new access token
         * and refresh token to use.
         *
         * For more information:
         * https://github.com/applicake/doorkeeper/wiki/Enable-Refresh-Token-Credentials
         *
         * */
        protected function makeSecureOauthCall($request_uri)
        {
            $oauth_server       = self::$oauth_server ?: self::setOauthServer();
            $url                = $oauth_server . $request_uri;

            if ($this->accessTokenExpired()) {
                $this->getAuthorizeToken();
            }

            $access_token = ADMWPP\Settings::instance()->getSettingsOption('account', 'access_token');

            $post_body = array(
                'access_token' => $access_token
            );

            $response = wp_remote_get(
                $url,
                array(
                    'body'      => $post_body,
                    'sslverify' => false,
                )
            );

            // If the response gave us an error, return.
            if (is_wp_error($response)) {
                return false;
            }

            if ($response['response']['code'] === 200) {
                return json_decode($response['body']);
            } else {
                return false;
            }
        }

        /**
        * Function To Check if the existing access token has expired.
        */
        public function accessTokenExpired()
        {
            $expires_in = (int) ADMWPP\Settings::instance()->getSettingsOption('account', 'expires_in');
            $created_at = (int) ADMWPP\Settings::instance()->getSettingsOption('account', 'created_at');

            $grace_period   = (int) ($created_at + ($expires_in * self::TOKEN_GRACE_PERIOD));
            $expires_on     = $created_at + $expires_in;

            $current_time = new \DateTime();

            if ($expires_on <= $current_time->getTimestamp()) {
                return self::TOKEN_EXPIRY_STATUS_EXPIRED;
            }

            if ($grace_period <= $current_time->getTimestamp()) {
                return self::TOKEN_EXPIRY_STATUS_GRACE_PERIOD;
            }

            return self::TOKEN_EXPIRY_STATUS_VALID;
        }

        /**
        * Function to get a Refresh token.
        */
        public function refreshToken()
        {
            $app_id         = ADMWPP\Settings::instance()->getSettingsOption('account', 'app_id');
            $app_secret     = ADMWPP\Settings::instance()->getSettingsOption('account', 'app_secret');
            $refresh_token  = ADMWPP\Settings::instance()->getSettingsOption('account', 'refresh_token');

            $oauth_server   = self::$oauth_server ?: self::setOauthServer();

            //Request Token
            $url = $oauth_server . "oauth/token";
            $post_body = array(
                'grant_type'    => 'refresh_token',
                'refresh_token' => $refresh_token,
                'client_id'     => $app_id,
                'client_secret' => $app_secret,
            );
            $response = wp_remote_post(
                $url,
                array(
                    'body'      => $post_body,
                    'sslverify' => false,
                )
            );

            return $this->saveAccessToken($response);
        }

        /**
        * Function to get a Access token.
        */
        protected function fetchAccessToken($code)
        {
            $redirect_uri = self::$redirect_uri ?: self::setRedirectUri();
            $oauth_server = self::$oauth_server ?: self::setOauthServer();

            $app_id     = ADMWPP\Settings::instance()->getSettingsOption('account', 'app_id');
            $app_secret = ADMWPP\Settings::instance()->getSettingsOption('account', 'app_secret');

            $grant_type = 'authorization_code';

            //Request Token
            $url = $oauth_server . "oauth/token";
            $post_body = array(
                'grant_type' =>     $grant_type,
                'code' =>           $code,
                'client_id' =>      $app_id,
                'client_secret' =>  $app_secret,
                'redirect_uri' =>   $redirect_uri,
            );

            $response = wp_remote_post(
                $url,
                array(
                    'body'      => $post_body,
                    'sslverify' => false,
                )
            );

            return $this->saveAccessToken($response);
        }

        /**
         * Sets the Redirect URI.
         *
         * @return void
         *
         * */
        protected static function setRedirectUri()
        {
            self::$redirect_uri = ADMWPP_URL_ROUTES .'?_uri=oauth/callback';
        }

        /**
         * Checks APP Environment and sets the oAuth server path accordingly.
         *
         * @return void
         *
         * */
        protected static function setOauthServer()
        {
            global $ADMWPP_APP_ENVIRONMENT;
            $env = ADMWPP_ENV;

            if (!empty($env)) {
                self::$oauth_server = $ADMWPP_APP_ENVIRONMENT[$env]['oauthServer'];
            } else {
                self::$oauth_server = $ADMWPP_APP_ENVIRONMENT['sandbox']['oauthServer'];
            }
        }
    }
}
