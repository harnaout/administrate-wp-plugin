<?php
namespace ADM\WPPlugin\Oauth2;

use Administrate\PhpSdk\Oauth\Activator;

use ADM\WPPlugin as ADMWPP;
use ADM\WPPlugin\Settings;

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
        public function setParams($weblink = false)
        {
            global $ADMWPP_APP_ENVIRONMENT;

            self::$params = $ADMWPP_APP_ENVIRONMENT[ADMWPP_ENV];

            if ($weblink) {
                self::$params = $ADMWPP_APP_ENVIRONMENT[ADMWPP_ENV]['weblink'];
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
        public function getAuthorizeUrl()
        {
            $instance = Settings::instance()->getSettingsOption('account', 'instance');
            $appId = Settings::instance()->getSettingsOption('account', 'app_id');
            $appSecret = Settings::instance()->getSettingsOption('account', 'app_secret');

            if (admwppBlank($instance) || admwppBlank($appId) || admwppBlank($appSecret)) {
                return;
            }

            $params = self::$params;
            $params['instance'] = $instance;
            $params['clientId'] = $appId;

            self::$activationObj->setParams($params);
            return self::$activationObj->getAuthorizeUrl();
        }

        /**
         * Function get Authorization token
         *
         * @return $authorize_token, array, the authorization Token Array.
         *
         * */
        public function getAuthorizeToken($weblink = false)
        {
            if ($weblink) {
                $accessToken = Settings::instance()->getSettingsOption('account', 'portal_token');

                $expiryStatus = $this->accessTokenExpired($weblink);

                if ($expiryStatus === self::TOKEN_EXPIRY_STATUS_GRACE_PERIOD) {
                    $tokentatus = Settings::instance()->getSettingsOption('account', 'portal_token_status');
                    if ($tokentatus === self::TOKEN_STATUS_RENEWING) {
                        return $accessToken;
                    }
                } elseif ($expiryStatus === self::TOKEN_EXPIRY_STATUS_VALID) {
                    return $accessToken;
                }

                if (!$this->renewAuthorizeToken($weblink)) {
                    return $accessToken;
                }

                $accessToken = Settings::instance()->getSettingsOption('account', 'portal_token');

                return $accessToken;
            }

            $accessToken = Settings::instance()->getSettingsOption('account', 'access_token');
            $tokenType   = Settings::instance()->getSettingsOption('account', 'token_type');

            $authorizeToken = array(
                'token'  => $accessToken,
                'type' => $tokenType
            );

            $expiryStatus = $this->accessTokenExpired();

            if ($expiryStatus === self::TOKEN_EXPIRY_STATUS_GRACE_PERIOD) {
                $tokentatus = Settings::instance()->getSettingsOption('account', 'token_status');
                if ($tokentatus === self::TOKEN_STATUS_RENEWING) {
                    return $authorizeToken;
                }
            } elseif ($expiryStatus === self::TOKEN_EXPIRY_STATUS_VALID) {
                return $authorizeToken;
            }

            if (!$this->renewAuthorizeToken()) {
                return $authorizeToken;
            }

            $authorizeToken['token'] = Settings::instance()->getSettingsOption('account', 'access_token');
            $authorizeToken['type']  = Settings::instance()->getSettingsOption('account', 'token_type');

            return $authorizeToken;
        }

        /**
         * Function To save new authorization token
         * It fetches the necessary app id and secret from the saved options.
         * */
        public function renewAuthorizeToken($weblink = false)
        {
            if ($weblink) {
                Settings::instance()->setSettingsOption(
                    'account',
                    'posrtal_token_status',
                    self::TOKEN_STATUS_RENEWING
                );

                $portal = Settings::instance()->getSettingsOption('account', 'portal');
                if ($portal) {
                    return $this->fetchWeblinkAccessToken($portal);
                }
            }

            Settings::instance()->setSettingsOption(
                'account',
                'token_status',
                self::TOKEN_STATUS_RENEWING
            );

            $appId = Settings::instance()->getSettingsOption('account', 'app_id');
            $clientSecret = Settings::instance()->getSettingsOption('account', 'app_secret');
            $refreshToken = Settings::instance()->getSettingsOption('account', 'refresh_token');

            if (admwppBlank($appId) || admwppBlank($clientSecret) || admwppBlank($refreshToken)) {
                return;
            }

            $params = self::$params;
            $params['clientId'] = $appId;
            $params['clientSecret'] = $clientSecret;
            self::$activationObj->setParams($params);
            $response = self::$activationObj->refreshTokens($refreshToken);

            return $this->saveAccessToken($response);
        }

        /**
        * Function to get a Save Access Token.
        */
        protected function saveAccessToken($response)
        {
            // If the response gave us an error, return.
            if ($response['status'] != "success") {
                Settings::instance()->setSettingsOption(
                    'account',
                    'token_status',
                    self::TOKEN_STATUS_ERROR
                );
                return false;
            }

            if ($response['status'] === "success") {
                $body = $response['body'];

                $access_token = $body->access_token;
                $token_type = $body->token_type;
                $expires_in = $body->expires_in;
                $scope = $body->scope;
                $refresh_token = $body->refresh_token;
                $created_at = current_time('timestamp');

                Settings::instance()->setSettingsOption('account', 'access_token', $access_token);
                Settings::instance()->setSettingsOption('account', 'token_type', $token_type);
                Settings::instance()->setSettingsOption('account', 'expires_in', $expires_in);
                Settings::instance()->setSettingsOption('account', 'created_at', $created_at);
                Settings::instance()->setSettingsOption('account', 'scope', $scope);
                Settings::instance()->setSettingsOption('account', 'refresh_token', $refresh_token);

                Settings::instance()->setSettingsOption(
                    'account',
                    'token_status',
                    self::TOKEN_STATUS_NEW
                );

                return true;
            } else {
                Settings::instance()->setSettingsOption(
                    'account',
                    'token_status',
                    self::TOKEN_STATUS_ERROR
                );
                return false;
            }
        }

        /**
        * Function to get a Save Portal Token.
        */
        protected function savePortalToken($response)
        {
            // If the response gave us an error, return.
            if ($response['status'] != "success") {
                Settings::instance()->setSettingsOption(
                    'account',
                    'portal_token_status',
                    self::TOKEN_STATUS_ERROR
                );
                return false;
            }

            if ($response['status'] === "success") {
                $body = $response['body'];

                $portal_token = $body->portal_token;
                $created_at = current_time('timestamp');
                $expires_in = 60 * (int) ADMWPP_PORTAL_TOKEN_EXPIRY_PERIOD;

                Settings::instance()->setSettingsOption('account', 'portal_token', $portal_token);
                Settings::instance()->setSettingsOption('account', 'portal_token_created_at', $created_at);
                Settings::instance()->setSettingsOption('account', 'portal_token_expires_in', $expires_in);

                Settings::instance()->setSettingsOption(
                    'account',
                    'portal_token_status',
                    self::TOKEN_STATUS_NEW
                );
                return true;
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

                $portal = Settings::instance()->getSettingsOption('account', 'portal');
                if ($portal) {
                    if (!$this->fetchWeblinkAccessToken($portal)) {
                        return false;
                    }
                }

                return true;
            } else {
                return false;
            }
        }

        /**
        * Function To Check if the existing access token has expired.
        */
        public function accessTokenExpired($weblink = false)
        {
            if ($weblink) {
                $expires_in = (int) Settings::instance()->getSettingsOption('account', 'portal_token_expires_in');
                $created_at = (int) Settings::instance()->getSettingsOption('account', 'portal_token_created_at');
            } else {
                $expires_in = (int) Settings::instance()->getSettingsOption('account', 'expires_in');
                $created_at = (int) Settings::instance()->getSettingsOption('account', 'created_at');
            }

            $grace_period   = (int) ($created_at + ($expires_in * self::TOKEN_GRACE_PERIOD));
            $expires_on     = $created_at + $expires_in;

            $current_time = current_time('timestamp');

            if ($expires_on <= $current_time) {
                return self::TOKEN_EXPIRY_STATUS_EXPIRED;
            }

            if ($grace_period <= $current_time) {
                return self::TOKEN_EXPIRY_STATUS_GRACE_PERIOD;
            }

            return self::TOKEN_EXPIRY_STATUS_VALID;
        }

        /**
        * Function to get a Access token.
        */
        protected function fetchAccessToken($code)
        {
            $appId = Settings::instance()->getSettingsOption('account', 'app_id');
            $clientSecret = Settings::instance()->getSettingsOption('account', 'app_secret');
            $instance = Settings::instance()->getSettingsOption('account', 'instance');

            $params = self::$params;
            $params['clientId'] = $appId;
            $params['clientSecret'] = $clientSecret;
            $params['instance'] = $instance;

            self::$activationObj->setParams($params);
            $response = self::$activationObj->handleAuthorizeCallback(array('code' => $code));

            return $this->saveAccessToken($response);
        }

        /**
        * Function to get a Weblink Access token.
        */
        protected function fetchWeblinkAccessToken($portal)
        {
            self::setParams(true);
            $params = self::$params;
            $params['portal'] = $portal;

            self::$activationObj->setParams($params);
            $response = self::$activationObj->getWeblinkPortalToken();

            return $this->savePortalToken($response);
        }

        /**
         * Sets the Redirect URI.
         *
         * @return void
         *
         * */
        protected static function setRedirectUri()
        {
            //self::$redirect_uri = ADMWPP_URL_ROUTES .'?_uri=oauth/callback';
            self::$redirect_uri = site_url() . "/wp-json/admwpp/oauth/callback";
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
