<?php

namespace Administrate\PhpSdk\Oauth;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

if (!class_exists('Activator')) {

    /**
     * This class is responsible for activating the plugin through oAuth
     * with the main Bookwitty Accounts app.
     *
     * @package default
     *
     */
    class Activator
    {
        protected static $instance;
        public $params;

        private const SUCCESS_CODE = 200;
        private const STATUS_SUCCESS = 'success';
        private const STATUS_ERROR = 'error';

        /**
         * Default constructor.
         * Set the static variables.
         *
         * @return void
         *
         */
        public function __construct($params = array())
        {
            $this->setParams($params);
        }

        /**
         * Static Singleton Factory Method
         * Return an instance of the current class if it exists
         * Construct a new one otherwise
         *
         * @return Activator object
         *
         */
        public static function instance()
        {
            if (!isset(self::$instance)) {
                $className = __CLASS__;
                self::$instance = new $className();
            }
            return self::$instance;
        }

        /**
         * Method to set APP Environment Params
         * @param array $params configuration array
         *
         * @return void
         */
        public function setParams($params)
        {
            $this->params = $params;
        }

        /**
         * Function to build the authorization URL.
         * It fetches the necessary app id and secret from the saved options.
         *
         * @return $requestUrl, string, the authorization URL.
         *
         * */
        public function getAuthorizeUrl()
        {
            $clientId = $this->params['clientId'];
            $oauthServer = $this->params['oauthServer'];

            $requestUrl  = $oauthServer;
            $requestUrl .= "/authorize?response_type=code";
            $requestUrl .= "&client_id=" . $clientId;

            if (isset($this->params['instance']) && !empty($this->params['instance'])) {
                $requestUrl .= "&instance=" . $this->params['instance'];
            }

            if (isset($this->params['redirectUri']) && !empty($this->params['redirectUri'])) {
                $requestUrl .= "&redirect_uri=" . $this->params['redirectUri'];
            }

            return $requestUrl;
        }

        /**
         * Function to handle authorize callback.
         * Checks for received authorization code,
         * And Sends the code using Post to  authorization server.
         *
         * If success set variables and return true to controller.
         * If Fails return empty array.
         *
         * @param  array  $params URL Params ($_GET)
         * @return array          Response array
         */
        public function handleAuthorizeCallback($params = array())
        {
            // If the callback is the result of an authorization call to
            // the oAuth server:
            //      - Ask for the access token
            if (isset($params['code']) && !empty($params['code'])) {
                $response = $this->fetchAccessTokens($params['code']);
                if (self::STATUS_SUCCESS === $response['status']) {
                    return $response;
                }
            }
            return array();
        }

       /**
        * Function To Check if the existing access token has expired.
        * @param  timestamp   $expiresOnDate date timeStamp
        * @return bool                       true / false
        */
        public function accessTokenExpired($expiresOnDate)
        {
            $utcTimezone = new \DateTimeZone('UTC');
            $expirationDate = new \DateTime($expiresOnDate, $utcTimezone);
            $now = new \DateTime(strtotime(DATE_FORMAT), $utcTimezone);

            return $expirationDate < $now;
        }

        /**
         * Function to get a new set of token tokens
         * from an previous refresh token
         * @param  string $refreshToken saved refresh token
         * @return object response
         */
        public function refreshTokens($refreshToken)
        {
            if (empty($refreshToken)) {
                return;
            }

            $clientId = $this->params['clientId'];
            $clientSecret = $this->params['clientSecret'];
            $oauthServer = $this->params['oauthServer'];
            $instance = $this->params['instance'];

            $grantType = 'refresh_token';

            //Request Token
            $url = $oauthServer . "/token";
            $requestArgs['form_params'] = array(
                'refresh_token' => $refreshToken,
                'grant_type' => $grantType,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            );

            $guzzleClient = new Client();
            $response = $guzzleClient->request('POST', $url, $requestArgs);

            return $this->proccessResponse($response);
        }

        /**
         * Function to get a new set of tokens
         * @param  string $refreshToken saved refresh token
         * @return object response
         */
        public function fetchAccessTokens($code)
        {
            $clientId = $this->params['clientId'];
            $clientSecret = $this->params['clientSecret'];
            $oauthServer = $this->params['oauthServer'];
            $lmsInstance = $this->params['instance'];

            $redirectUri = '';
            if (isset($this->params['redirectUri']) && !empty($this->params['redirectUri'])) {
                $redirectUri = $this->params['redirectUri'];
            }

            $grantType = 'authorization_code';

            //Request Token
            $url = $oauthServer . "/token";
            $requestArgs['form_params'] = array(
                'grant_type' =>     $grantType,
                'code' =>           $code,
                'client_id' =>      $clientId,
                'client_secret' =>  $clientSecret,
            );

            if ($redirectUri) {
                $requestArgs['form_params']['redirect_uri'] = $redirectUri;
            }

            $guzzleClient = new Client();
            $response = $guzzleClient->request('POST', $url, $requestArgs);

            return $this->proccessResponse($response);
        }

        /**
         * Function to get a new weblink portal token
         * @return object response
         */
        public function getWeblinkPortalToken()
        {
            $oauthServer = $this->params['oauthServer'];
            $portal = $this->params['portal'];

            //Request Token
            $url = $oauthServer . "/portal/guest";

            $requestArgs['headers'] =  array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json, text/plain, */*'
            );

            $body = '{"domain":"' . $portal . '"}';
            $requestArgs['body'] = $body;

            $guzzleClient = new Client();
            $response = $guzzleClient->request('POST', $url, $requestArgs);

            return $this->proccessResponse($response);
        }

        /**
         * Method to process guzzle client Response
         * and return results to be saved.
         *
         * @param  object $response Guzzle Response Object.
         * @return array response
         */
        protected function proccessResponse($response)
        {
            $code = $response->getStatusCode();
            $result = array();
            if (self::SUCCESS_CODE === $response->getStatusCode()) {
                $body = $response->getBody();
                $result['status'] = self::STATUS_SUCCESS;
                $result['body'] = json_decode($body);
            } else {
                $result['status'] = self::STATUS_ERROR;
                $result['error'] = array(
                    'code' => $code,
                    'message' => $response->getReasonPhrase()
                );
            }
            return $result;
        }
    }
}
