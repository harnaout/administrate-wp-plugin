<?php
namespace ADM\WPPlugin;

use ADM\WPPlugin\Controllers as Controllers;

//Check that zlib compression is enabled and disable it.
if (ini_get('zlib.output_compression')) {
    ini_set("zlib.output_compression", "Off");
}

if (file_exists('../../../../wp-load.php')) {
    require_once('../../../../wp-load.php');
}

if (!class_exists('Router')) {

    /**
     * This will be the ADMinistrate plugin's main routes.
     * It will handle figuring out which controller/action to go to
     * based on the passed parameters and the HTTP verbs, REST style.
     *
     * @package default
     *
     */
    class Router
    {
        protected static $instance;
        static $controllers_namespace = "ADM\WPPlugin\Controllers\\";
        static $action;
        static $controller;
        static $format;
        static $method;
        static $params;
        static $routes;
        static $debug;
        static $uri;

        /**
         * Default constructor.
         *
         * @return void
         *
         */
        protected function __construct()
        {
        }

        /**
         * Static Singleton Factory Method
         * Return an instance of the current class if it exists
         * Construct a new one otherwise
         *
         * @return Main object
         *
         */
        public static function instance()
        {
            if (!isset(static::$instance)) {
                $name = __CLASS__;
                static::$instance = new $name;
            }
            return static::$instance;
        }

        /**
         * Run the appropriate action.
         * Parse the params then accordingly determine which action to use.
         *
         * @return void
         */
        public static function run($args)
        {
            static::parseParams($args);
            static::setDebug();
            static::setUri();
            static::route();
        }

        /**
         * Set the $params attribute depending on the request method.
         *
         * @return void
         */
        public static function parseParams($args = array())
        {
            switch ($args['method']) {
                case 'GET':
                    static::$method = 'GET';
                    static::$params = $_GET;
                    static::setFormat();
                    break;
                case 'POST':
                    // Using rails.js, we set links with data-method="delete"
                    // which will set $_REQUEST['_method'] = 'delete'.
                    if (isset($_REQUEST['_method']) && 'delete' === $_REQUEST['_method']) {
                        unset($_REQUEST['_method']);
                        static::$method = 'DELETE';
                        static::$params = $_REQUEST;
                        static::setFormat();
                        break;
                    }

                    // Since in PHP, we can only use POST and GET, we will follow
                    // the rails.js way of setting $_REQUEST['_method'] = 'put'.
                    if (isset($_REQUEST['_method']) && 'put' === $_REQUEST['_method']) {
                        unset($_REQUEST['_method']);
                        static::$method = 'PUT';
                        static::$params = $_REQUEST;
                        static::setFormat();
                        break;
                    }

                    static::$method = 'POST';
                    static::$params = $_POST;
                    static::setFormat();
                    break;
                case 'PUT':
                    static::$method = 'PUT';
                    parse_str(file_get_contents("php://input"), static::$params);
                    static::setFormat();
                    break;
                case 'DELETE':
                    static::$method = 'DELETE';
                    static::$params = $_REQUEST;
                    static::setFormat();
                    break;
                default:
                    break;
            }
        }

        public static function setFormat()
        {
            static::$format = isset(static::$params['_format']) ? static::$params['_format'] : 'html';
            unset(static::$params['_format']);
        }

        public static function setUri()
        {
            static::$uri = @static::$params['_uri'];
            unset(static::$params['_uri']);
        }

        public static function setDebug()
        {
            static::$debug = @static::$params['_debug'] || false;
            unset(static::$params['_debug']);
        }

        public static function addRoute($method, $url, $callback)
        {
            static::$routes[] = array('method' => $method, 'url' => $url, 'callback' => $callback);
        }

        // Add all RESTful actions for a resource.
        public static function resource($resource)
        {
            Router::addRoute('GET', "$resource", "$resource#index");
            Router::addRoute('GET', "$resource/new", "$resource#_new");
            Router::addRoute('POST', "$resource", "$resource#create");
            Router::addRoute('GET', "$resource/:id", "$resource#show");
            Router::addRoute('GET', "$resource/:id/edit", "$resource#edit");
            Router::addRoute('POST', "$resource/:id", "$resource#update");
            Router::addRoute('PUT', "$resource/:id", "$resource#update");
            Router::addRoute('PATCH', "$resource/:id", "$resource#update");
            Router::addRoute('PUT', "$resource/:id/status", "$resource#status");
            Router::addRoute('DELETE', "$resource/:id", "$resource#destroy");
        }

        public static function matchUriKeysWithValues($keys, $values)
        {
            foreach ($keys as $index => $key) {
                static::$params[$key] = $values[$index];
            }
        }

        /**
         * Determine what the controller and action are from the callback string.
         * Then set them in the static variables of the class;
         *
         * @param $callback, String. eg: 'selections#index'.
         * @return void
         */
        public static function parseControllerAndAction($callback)
        {
            // Callback is a string of the format: controller_name#action_name.
            // Separate the controller from the action.
            $temp = explode('#', $callback);

            // Turn the controller name to a proper class name.
            $controller  = str_replace('_', ' ', $temp[0]);
            $controller  = ucwords($controller);
            $controller  = str_replace(' ', '', $controller);
            $controller .= 'Controller';

            static::$controller   = static::$controllers_namespace . $controller;
            static::$action       = $temp[1];
        }

        public static function passOnParamsToController()
        {
            $controller = static::$controller;
            $controller::$format = static::$format;
            $controller::$method = static::$method;
            $controller::$params = static::$params;
        }

        public static function route()
        {
            // This pattern matches the keys for the ids set in the route.
            // eg. /users/:id                   => id
            // eg. /users/:user_id/posts/:id    => user_id, id
            // A more complex version of it: \:((?:[a-z\_]+))\/{0,1}.*?
            $keys_pattern   = '/\\:([a-zA-Z0-9\_\-]+)/';
            $routes         = static::$routes;

            if (static::$debug) {
                echo '<h2>URI:</h2>';
                echo '<pre>';
                echo static::$uri;
                echo '</pre>';

                echo '<h2>Routes:</h2>';
                echo '<pre>';
                $routes_table;
                ob_start();
                include(ADMWPP_TEMPLATES_DIR . 'development/routes.phtml');
                $routes_table =  ob_get_contents();
                ob_end_clean();
                echo $routes_table;
                echo '</pre>';
            }

            foreach ($routes as $route) {
                // convert urls like '/users/:uid/posts/:pid' to regular expression
                $pattern = "@^" . preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_]+)', preg_quote($route['url'])) . "$@D";

                $values = array();
                $keys   = array();

                // check if the current request matches the expression
                if (static::$method == $route['method'] && preg_match($pattern, static::$uri, $values)) {
                    // remove the first match because it usually is the full string
                    array_shift($values);

                    preg_match_all($keys_pattern, $route['url'], $keys);
                    // remove the first match because it usually is the full string
                    $keys = $keys[1];

                    // Set the params values for the keys defined in the route
                    // if any.
                    static::matchUriKeysWithValues($keys, $values);

                    static::parseControllerAndAction($route['callback']);
                    static::passOnParamsToController();

                    if (static::$debug) {
                        echo '<h2>Route Matched:</h2>';
                        echo '<pre>';
                        $route_table;
                        ob_start();
                        include(ADMWPP_TEMPLATES_DIR . 'development/_routes_table_head.php');
                        include(ADMWPP_TEMPLATES_DIR . 'development/route.php');
                        include(ADMWPP_TEMPLATES_DIR . 'development/_routes_table_footer.php');
                        $route_table =  ob_get_contents();
                        ob_end_clean();
                        echo $route_table;
                        echo '</pre>';

                        echo '<h2>Params:</h2>';
                        echo '<pre>';
                        print_r(static::$params);
                        echo '</pre>';

                        echo '<h2>Controller:</h2>';
                        echo '<pre>';
                        echo static::$controller;
                        echo '</pre>';

                        echo '<h2>Action:</h2>';
                        echo '<pre>';
                        echo static::$action;
                        echo '</pre>';
                    }

                    call_user_func(array( static::$controller, static::$action ));

                    return;
                }
            }

            if (static::formatIsJson()) {
                $pattern = static::$method;
                $response = array(
                    'status'    => 'error',
                    'message'   => "No route was found that matches the pattern $pattern.",
                );
                echo json_encode($response);
                return;
            } else {
                if (static::$debug) {
                    echo "No route was found that matches the pattern $pattern.";
                } else {
                    throw new \Exception("No route was found that matches the pattern $pattern.");
                }
            }
        }

        protected static function formatIsJson()
        {
            $class = get_called_class();
            return (!empty($class::$format) && 'json' == $class::$format);
        }

        protected static function formatIsHtml()
        {
            $class = get_called_class();
            return (empty($class::$format) || 'html' == $class::$format);
        }
    }
}
