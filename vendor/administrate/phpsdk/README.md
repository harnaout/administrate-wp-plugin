# Administrate PHP SDK

PHP SDK which provides a simple way to interact with administrate platform.
Facilitate authorization to the APIs and Provides ways to use the available APIs.


## Note

In order to use this library, please contact <support@getadministrate.com> to provide you with the needed credentials (clientId, clientSecret, instance url and portal).\
Or\
You can create an account on administrate [developers](https://developer.getadministrate.com/) environment and test your integration.

## Installation

Using [composer](https://getcomposer.org/)

```composer
composer require administrate/phpsdk
```

## Usage

This SDk is built to consume both core API and weblink API, each has a different way in authorization.

The steps to authorize with core API are:\
1 - request authorization code\
2 - request access and refresh tokens using code from step 1\
3 - refresh your tokens after access token is expired (only do this if needed, not a neccessary step in aythorization)
### Authorization with Core API - Request Authorization Code
```php
require_once 'vendor/autoload.php';

use Administrate\PhpSdk\Oauth\Activator;

$coreApiActivationParams = [
    'clientId' => '9juZ...Ig7U',     // Application ID
    'clientSecret' => 'd1RN...qt2h', // Application secret
    'instance' => 'https://YourInstanse.administrateapp.com/',     // Administrate instance to connect to
    'oauthServer' => 'https://auth.getadministrate.com/oauth',  // Administrate authorization endpoint
    'apiUri' => 'https://api.administrateapp.com/graphql', // Administrate Core API endpoint
    'redirectUri' => 'https://YourAppDomain/callback.php',  // Your app redirect URI to handle callbacks from api
    'accessToken' => 'ACCESS_TOKEN_HERE',  //in this step we don't have it yet
    'refreshToken' => 'REFRESH_TOKEN_HERE' //in this step we don't have it yet
];

// Create Activate Class instance
$activationObj = new Activator($coreApiActivationParams));

// Get Authorization Code:
$urlToGoTo = $activationObj->getAuthorizeUrl();
```

##### Example URL output:
*https://auth.getadministrate.com/oauth/authorize?response_type=code&client_id=9juZ...Ig7U&instance=https://YourInstanse.administrateapp.com/&redirect_uri=https://YourAppDomain/callback.php*

The Previous code will create a link for you to go to.\
This link will redirect you to the login screen of your instance mentioned in the params with a redirect to link setup for the URL of your choice.\
Once you login to the instance of administrate you will be promoted to authorize the APP.\
Once done you will be redirected to the callback url with the code in the url (ex: YOUR_CALLBACK_URL/?code=CODE_HERE).\
\
Add the code in your config file it will be used in the get tokens method.\
Note that this is a one time use code.

*Check [oauth-get-authorization-code.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/authentication/oauth-get-authorization-code.php) in examples folder*

### Authorization with Core API - Request access token and refresh token

##### Example Callback url:
*https://YourAppDomain/callback.php?code=9juZ...Ig7U*

```php
require_once 'vendor/autoload.php';

use Administrate\PhpSdk\Oauth\Activator;

//$authorizationCode is the code we got from previous step
$authorizationCode = '';

//same activationParams as before
$activationObj = new Activator($coreApiActivationParams));

// Handle Callback.
$response = $activationObj->handleAuthorizeCallback( array( 'code' => $authorizationCode) );
// This method will trigger sending an access token request using
// "fetchAccessTokens".
// The returned response is an multidimensional array
// with a status and body.
// In the body you have an access_token and a refresh_token
// You should use the access_token in your request header
// as Authorization Bearer in order for you to be granted access to the Core API.

// Or you can get the code from the callback URL
// and pass it as arg to the following method.
$response = $activationObj->fetchAccessTokens($authorizationCode);

// Response Format (array):
{
    "status" => "success",
    "body" => {
        "access_token" => "sWNRpcf.....106vqR4",
        "expires_in"=> 3600,
        "token_type" => "Bearer",
        "scope" => "instance",
        "refresh_token" => "StEqsly.....V5nUhQd1i"
    }
}
```
*Check [oauth-get-tokens.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/authentication/oauth-get-tokens.php) in examples folder*

You should save the **access_token** to be used with your calls to the API.\
You should save the **expires_in** to calculate when the **access_token** expires and request a new one.\
You should save the **refresh_token** to be used later to get a new **access_token** once it expires.

### Authorization with Core API - Refresh Token
```php
require_once 'vendor/autoload.php';

use Administrate\PhpSdk\Oauth\Activator;

//same activationParams as before
$activationObj = new Activator($activationParams));

//$refresh_token value previously saved

// Request an new Access Token.
$response = $activate->refreshTokens($refresh_token);

// Response Format (array):
{
    "status" => "success",
    "body" => {
        "access_token" => "sWNRpcf.....106vqR4",
        "expires_in"=> 3600,
        "token_type" => "Bearer",
        "scope" => "instance",
        "refresh_token" => "StEqsly.....V5nUhQd1i"
    }
}
```
*Check [oauth-refresh-tokens.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/authentication/oauth-refresh-tokens.php) in examples folder*

### Authorization with Weblink API
```php
require_once 'vendor/autoload.php';

use Administrate\PhpSdk\Oauth\Activator;

$activationParams = [
    'oauthServer' => 'https://portal-auth.administratehq.com', // Administrate weblink authorization endpoint
    'apiUri' => 'https://weblink-api.administratehq.com/graphql', // Administrate Weblink endpoint
    'portal' => 'APPNAME.administrateweblink.com',
];

// Create Activate Class instance
$activationObj = new Activator($activationParams));

$response = $activationObj->getWeblinkCode();

// this method willreturn the portal token as a string
```
*Check [get-weblink-portal-token.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/authentication/get-weblink-portal-token.php) in examples folder*

### Categories Management

*You need a portal token to be able to list categories*

#### List Categories
```php
require_once '/vendor/autoload.php';

use Administrate\PhpSdk\Category;

$params = [
    'oauthServer' => 'https://portal-auth.administratehq.com', // Administrate weblink authorization endpoint
    'apiUri' => 'https://weblink-api.administratehq.com/graphql', // Administrate Weblink endpoint
    'portal' => 'APPNAME.administrateweblink.com',
    'portalToken' => 'Tcdg...DIY9o',
];

$categoryObj = new Category($params);

$categoryId = "TGVh....YeTox";
$args = [
    'filters' => [
        // [
        //     'field' => 'name',
        //     'operation' => 'eq',
        //     'value' => 'Example Category 5',
        // ]
    ],
    'paging' => [
        'page' => 1,
        'perPage' => 2
    ],
    'sorting' => [
        'field' => 'name',
        'direction' => 'asc'
    ],
    'returnType' => 'json', //array, obj, json,
    'fields' => [
        'id',
        'name',
    ],
];

// Get Single Category
$category = $categoryObj->loadById($categoryId, $args);

// Get all categories
$categories = $categoryObj->loadAll($args);

```
*Check [get-single.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/categories/get-single.php) and [get-multiple.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/categories/get-multiple.php) in examples folder*
*Check [get-single-coreAPI.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/categories/get-single-coreAPI.php) and [get-multiple-coreAPI.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/categories/get-multiple-coreAPI.php) in examples folder*

### Courses Management
### List Courses
```php
require_once '/vendor/autoload.php';

use Administrate\PhpSdk\Course;

$params = [
    'oauthServer' => 'https://portal-auth.administratehq.com', // Administrate weblink authorization endpoint
    'apiUri' => 'https://weblink-api.administratehq.com/graphql',
    'portal' => 'APPNAME.administrateweblink.com',
    'portalToken' => 'Tcdg...DIY9o',
];

$CourseObj = new Course($params);

$categoryId = "TGVh......eTox"; //optional
$keyword = "test_keyword_here"; //optional
$courseId = "TGVh......eTox";

$keyword = "Template 3";

$args = [
    'filters' => [
        // [
        //     "field" => "learningCategoryId",
        //     "operation" => "eq",
        //     "value" => $categoryId
        // ],
        // [
        //     "field" => "name",
        //     "operation" => "like",
        //     "value" => "%".$keyword."%"
        // ]
    ],
    'paging' => [
        'page' => 1,
        'perPage' => 2
    ],
    'sorting' => [
        'field' => 'name',
        'direction' => 'asc'
    ],
    'returnType' => 'json', //array, obj, json
    // 'fields' => [
    //     'id',
    //     'name'
    // ],
    'coreApi' => false, //boolean to specify if call is a weblink or a core API call.
);

//Get single course
$course = $CourseObj->loadById($courseId, $args);

//get Courses with filters
$categories = $courseObj->loadAll($args);

```
*Check [get-single.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/courses/get-single.php)
and
[get-multiple.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/courses/get-multiple.php) in examples folder*
*Check [get-single-coreAPI.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/courses/get-single-coreAPI.php)
and
[get-multiple-coreAPI.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/courses/get-multiple-coreAPI.php) in examples folder*

### Learning Paths Management
#### List Learning Paths
```php
require_once '/vendor/autoload.php';

use Administrate\PhpSdk\LearningPath;

$params = [
    'oauthServer' => 'https://portal-auth.administratehq.com', // Administrate weblink authorization endpoint
    'apiUri' => 'https://weblink-api.administratehq.com/graphql', // Administrate Weblink endpoint
    'portal' => 'APPNAME.administrateweblink.com',
    'portalToken' => 'Tcdg...DIY9o',
];

$learningPathObj = new LearningPath($params);

$learningPathId = "TGVh....YeTox";
$args = [
    'filters' => [
        // [
        //     'field' => 'name',
        //     'operation' => 'eq',
        //     'value' => 'Learning path test',
        // ]
    ],
    'paging' => [
        'page' => 1,
        'perPage' => 2
    ],
    'sorting' => [
        'field' => 'name',
        'direction' => 'asc'
    ],
    'returnType' => 'json', //array, obj, json,
    'fields' => [
        'id',
        'name',
    ],
];

// Get Single Learning Path
$learningPath = $learningPathObj->loadById($learningPathId, $args);

// Get all Learning paths
$learningPaths = $learningPathObj->loadAll($args);

```
*Check [get-single.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/learning-path/get-single.php) and [get-multiple.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/learning-path/get-multiple.php) in examples folder*
*Check [get-single-coreAPI.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/learning-path/get-single-coreAPI.php) and [get-multiple-coreAPI.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/learning-path/get-multiple-coreAPI.php) in examples folder*

### Events Management
### List Events
```php
require_once '/vendor/autoload.php';

use Administrate\PhpSdk\Event;

$params = [
    'oauthServer' => 'https://portal-auth.administratehq.com', // Administrate weblink authorization endpoint
    'apiUri' => 'https://weblink-api.administratehq.com/graphql',
    'portal' => 'APPNAME.administrateweblink.com',
    'portalToken' => 'Tcdg...DIY9o',
];

$EventObj = new Event($params);

$eventId = "TGVh......eTox";
$courseCode = "Tls6....c99na";

$args = [
    'filters' => [
        // [
        //     'field' => 'name',
        //     'operation' => 'eq',
        //     'value' => 'Learning path test',
        // ]
    ],
    'paging' => [
        'page' => 1,
        'perPage' => 2
    ],
    'sorting' => [
        'field' => 'name',
        'direction' => 'asc'
    ],
    'returnType' => 'json', //array, obj, json,
    'fields' => [
        'id',
        'name',
    ],
    'courseCode' => "&GKJy...@ejlkge^",
];

//Get single event
$event = $eventObj->loadById($eventId, $args);

//get all events
$events = $eventObj->loadAll($args);

//get all events for a single course
$events = $eventObj->loadByCourseCode($args);
```
*Check [get-single.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/events/get-single.php), [get-multiple.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/events/get-multiple.php) and [get-events-by-course.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/events/get-events-by-courset.php) in examples folder*
*Check [get-single-coreAPI.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/events/get-single-coreAPI.php), [get-multiple-coreAPI.php](https://github.com/Administrate/administrate-php-sdk/blob/trunk/examples/events/get-multiple-coreAPI.php) in examples folder*
## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)
