# Administrate WrodPress Plugin

Requires at least: 5.0.0

Tested up to: 5.3.2

Stable tag: 1.0.0

License: GPLv2 or later

License URI: http://www.gnu.org/licenses/gpl-2.0.html


## Description

Administrate WordPress Plugin to facilitate the integration and synchronization of TMS content into WordPress content and Taxonomies, with the ability to display the content in templates using short-codes / custom filters / template overrides.

## Installation

The plugin is not released on wordpress.org yet, so if you need to use it please contact Administrate by sending us en email at support@getadministrate.com and we will send you the files needed to install the plugin.

or

Clone the plugin to your project [Administrate WordPress Plugin](https://github.com/Administrate/administrate-wp-plugin)

**Note:** you can add it to the main WordPress project as a git sub-module.

### Steps:

1. Upload the zip files "administrate-wp-plugin.zip" to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Now go to the plugin my account settings and activate the plugin `/wp-admin/admin.php?page=admwpp-settings&tab=admwpp_account_settings`

![Authorization screen](/assets/images/authorization-screen.png)

4. The app credentials will be provided for you by the Administrate team (or you can get them from [Administrate Developer Portal](https://developer.getadministrate.com/))
5. Once you activate the plugin with the proper instance the plugin will automatically create a new post type `course` as well as a custom taxonomy `learning-category` as well to granting you access to more settings tabs.

![Settings menu](/assets/images/settings-menu.png)

6. Next go to advanced settings tab `/wp-admin/admin.php?page=admwpp-settings&tab=admwpp_advanced_settings` to import the courses/LPs and Learning categories.

![Courses Back fill](/assets/images/backfill.png)

7. Once done with the import you should be able to see that the TMS courses/LPS and Learning Categories under `/wp-admin/edit.php?post_type=course` are synced into WordPress and are available as new posts on the site. _(plugin will auto sync some of the core default fields such as title, description, excerpt, image, etc... but you can add other custom fields fields using [Custom Course Sync Filters](#course-sync-filters))_

![Courses menu](/assets/images/courses.png)

8. Finally to ensure the courses/LPs synced to WordPress are kept up to date you need to configure the Webhooks for the TMS to trigger WordPress to fetch updates.

![Webhooks config](/assets/images/webhooks-config.png)

*You can find additional documentation of how to use the plugin custom filters / Short-codes / template overrides in the [Developers section](#developers-section) below.
## How to Configure the Webhooks

On the Advanced settings tab scroll to `Setup Webhooks for Synchronization` section, you will find entries for each webhook type ID and another field for the created Webhook config ID, you only need to populate the Webhook Type IDs and hit the save button on the page, the plugin will automatically create those Webhooks configuration and set them to active you can view those on: `https://[TMS_INSTANCE_NAME].administrateapp.com/ux/settings/other/webhooks`
To get the Webhook Type IDs run the following GraphQl query `https://[TMS_INSTANCE_NAME].administrateapp.com/graphql`
```
query getWebhooksTypes {
  webhookTypes {
    edges {
      node {
        name
        description
        id
      }
    }
  }
}
```
**Example**: Course Update Webhook Type ID => should be the ID of the Webhook Type with title `Course Template Updated` with description of `A webhook will be triggered when a Course Template has been updated`
```
{
    "node": {
    "name": "Course Template Updated",
    "description": "A webhook will be triggered when a Course Template has been updated",
    "id": "V2ViaG9v....VwZGF0ZWQ=" <= This is the ID you need to use.
    }
}
```

To sanity check the webhooks have been properly configured:
```
query getWebhooks { webhooks {edges {node { name id url emailAddress query } } } }
```
Expected Result should be something similar to the bellow:
```
{ "node": {
"name": "Wordpress Trigger Course Template Updated",
"id": "T3V....jE=",
"url": "[YOUR_SITE_URL]/wp-json/admwpp/webhook/callback",
"emailAddress": "[WORDPRESS_ADMIN_EMAIL]",
"query": "query courses ($objectid: String!) {courseTemplates (filters: [{field: id, operation: eq, value: $objectid}]) { edges { node { id } } } }" } }
```
**Note:** Getting the Webhook Type IDs to use will be automated in the following versions of the plugin.

## ShortCodes
#### `[admwpp-gift-voucher]`
  * `option_id` can be found using GraphQl.
  * `currency_symbol` defaults to `ADMWPP_VOUCHER_CURRENCY`
  * `title` defaults to "Gift voucher"
  * `button_text` defaults to "Add Voucher"

  The gift voucher Short-code has some global defined Variables found in `globals.php` that can be overridden in `wp-config.php` if needed
  ```
  if (!defined('ADMWPP_MIN_VOUCHER_AMOUNT')) {
      define('ADMWPP_MIN_VOUCHER_AMOUNT', 1);
  }
  if (!defined('ADMWPP_MAX_VOUCHER_AMOUNT')) {
      define('ADMWPP_MAX_VOUCHER_AMOUNT', 250.00);
  }
  if (!defined('ADMWPP_VOUCHER_AMOUNT_STEP')) {
      define('ADMWPP_VOUCHER_AMOUNT_STEP', 1);
  }
  if (!defined('ADMWPP_VOUCHER_CURRENCY')) {
      define('ADMWPP_VOUCHER_CURRENCY', '€');
  }
  if (!defined('ADMWPP_VOUCHER_CURRENCY_CODE')) {
      define('ADMWPP_VOUCHER_CURRENCY_CODE', 'EUR');
  }
  if (!defined('ADMWPP_NOT_NUMBER_MESSAGE')) {
      define(
          'ADMWPP_NOT_NUMBER_MESSAGE',
          _x('Please enter a valid number', 'Gift Voucher', 'admwpp')
      );
  }
  if (!defined('ADMWPP_VOUCHER_EMPTY_AMOUNT_MESSAGE')) {
      define(
          'ADMWPP_VOUCHER_EMPTY_AMOUNT_MESSAGE',
          _x('Gift voucher is below the minimum value of %s %s', 'Gift Voucher', 'admwpp')
      );
  }
  if (!defined('ADMWPP_VOUCHER_MAX_AMOUNT_MESSAGE')) {
      define(
          'ADMWPP_VOUCHER_MAX_AMOUNT_MESSAGE',
          _x('Gift voucher is above the maximum value of %s %s', 'Gift Voucher', 'admwpp')
      );
  }
  if (!defined('ADMWPP_NO_WEBLINK')) {
      define('ADMWPP_NO_WEBLINK', 'Weblink not active');
  }
  ```

#### `[admwpp-my-workshops]`
  * `email` default is empty (we kept the user email empty as this value should be filled by a dependent plugin or on the theme level using), this `email` value can be passed as a short-code param or using a custom filter `admwpp_user_email_workshops`
  * `status` default is "active", the `status` value can be passed as a short-code param or using a custom filter `admwpp_user_status_workshops`

#### `[admwpp-bundled-lps]`
  * `page` defaults to 1
  * `per_page` defaults to `ADMWPP_PER_PAGE`
  * `ajax` defaults to `false`
  * `order_by` defaults to `name`
  * `order_direction` defaults to `asc`
  * `post_id` defaults to 0 <= this is required for this short-code to work.
  **If you need to have some custom filter injected to this short-code GraphQl Query you can use the custom filter `admwpp_bundled_lps_shortcode_args`**

#### `[admwpp-search-form]`
  * `pager` defaults to `simple` could be set to `full`
  * `template` defaults to `grid`,
  * `filters` comma separated list of filter to show on the page `category,date,location,dayofweek,minplaces,timeofday,types`
  * `per_page` defaults to `ADMWPP_SEARCH_PER_PAGE`
  * `categories_filter_type` defaults to `select`
  * `locations_filter_type` defaults to `select`
  **If you need to have some custom filter injected to this short-code GraphQl Query you can use the custom filter `admwpp_search_args`**

  The Search Form Shortcode has some global defined Variables found in `globals.php`  that can be overridden in `wp-config.php` if needed
  ```
  if (!defined('ADMWPP_SEARCH_DATE_DISPLAY_FORMAT')) {
    define('ADMWPP_SEARCH_DATE_DISPLAY_FORMAT', 'mm-dd-yy');
  }
  ```

# Developers Section

## Custom Filters
### Shortcode Filters
  * `admwpp_user_email_workshops`
    * **Defined:** `$email = apply_filters('admwpp_user_email_workshops', $email);`
    * **Usage:** `add_filter('admwpp_user_email_workshops', '[THEME_CUSTOM_METHOD]', 10, 1);`
  * `admwpp_user_status_workshops`
    * **Defined:** `$status = apply_filters('admwpp_user_status_workshops', $status);`
    * **Usage:** `add_filter('admwpp_user_status_workshops', '[THEME_CUSTOM_METHOD]', 10, 1);`
  * `admwpp_bundled_lps_shortcode_args`
    * **Defined:** `$args = apply_filters('admwpp_bundled_lps_shortcode_args', $args);`
    * **Usage:** `add_filter('admwpp_bundled_lps_shortcode_args', '[THEME_CUSTOM_METHOD]', 10, 1);`
    * **instructions**: Check [PHP SDK Example](https://github.com/Administrate/php-sdk/blob/1.10.0/examples/catalogue/get-multiple.php) or the method `getBundledLps` in `/src/Shortcodes/Shortcode.php`

### Search Filters
  * `admwpp_days_of_week_filter`
    * **Defined:** `$args = apply_filters('admwpp_days_of_week_filter', $ADMWPP_SEARCH_DAYSOFWEEK);`
    * **Usage:** `add_filter('admwpp_days_of_week_filter', '[THEME_CUSTOM_METHOD]', 10, 1);`
    * **instructions**: Check the Global Defined values in `/src/globals.php`
  * `admwpp_time_of_day_filter`
    * **Defined:** `$timeofdayFilter = apply_filters('admwpp_time_of_day_filter', $ADMWPP_SEARCH_TIMEOFDAY);`
    * **Usage:** `add_filter('admwpp_time_of_day_filter', '[THEME_CUSTOM_METHOD]', 10, 1);`
    * **instructions**: Check the Global Defined values in `/src/globals.php`
  * `admwpp_course_types_filter`
    * **Defined:** `$typesFilter = apply_filters('admwpp_course_types_filter', $ADMWPP_SEARCH_COURSES_TYPES);`
    * **Usage:** `add_filter('admwpp_course_types_filter', '[THEME_CUSTOM_METHOD]', 10, 1);`
    * **instructions**: Check the Global Defined values in `/src/globals.php`
  * `admwpp_search_args`
    * **Defined:** `$args = apply_filters('admwpp_search_args', $args);`
    * **Usage:** `add_filter('admwpp_search_args', '[THEME_CUSTOM_METHOD]', 10, 1);`
    * **instructions**: Check [PHP SDK Example](https://github.com/Administrate/php-sdk/blob/1.10.0/examples/catalogue/get-multiple.php) or the method `search` in `/src/Api/Search.php`

### Course Sync
  * `admwpp_course_args`
    * **Defined:** `$args = apply_filters('admwpp_course_args', $args);`
    * **Usage:** `add_filter('admwpp_course_args', '[THEME_CUSTOM_METHOD]', 10, 1);`
    * **instructions**: Check [WordPress documentation](https://developer.wordpress.org/reference/functions/register_post_type/)
  * `admwpp_tms_custom_fileds_maping`
    * **Defined:** `$args = apply_filters('admwpp_tms_custom_fileds_maping', $args);`
    * **Usage:** `add_filter('admwpp_tms_custom_fileds_maping', '[THEME_CUSTOM_METHOD]', 10, 2);`
    * **instructions**: You can check the Method definition for `getTmsCustomFiledsMapping` in `Course.php` for additional info on the params and structure of the multidimensional array for mapping custom fields to posts.
  * `admwpp_course_price_level_names`
    * **Defined:** `$priceLevelNames = apply_filters('admwpp_course_price_level_names', $priceLevelNames, $type, $customFields);`
    * **Usage:** `add_filter('admwpp_course_price_level_names', '[THEME_CUSTOM_METHOD]', 10, 3);`
    * **instructions**: This filter is used to set the custom price levels to be synced, it takes as parameter an array of price levels names `$priceLevelNames =  array('Normal');` to be stored in `admwpp_tms_price` and `admwpp_tms_currency` course meta separated by `|` mainly used for display on the course template page, other 2 prams are the Course Type and the CustomFileds mapped key to value array that can be used to apply some special condition if needed.
  * `admwpp_course_post_status`
    * **Defined:** `$postStatus = apply_filters('admwpp_course_post_status', $postStatus, $type, $postMetas, $node);`
    * **Usage:** `add_filter('admwpp_course_post_status', '[THEME_CUSTOM_METHOD]', 4, 10);`
    * **instructions**: This filter is used to alter the post status based on synced post meta values, Mainly to be used to apply some custom handling on special meta values condition based on client integrations, it take as params the default $postStatus based on TMS status as well Course Type the Wordpress post meta mapped meta_key to meta_value array as well as the raw node object from the GrapgQl query result.
  * `admwpp_course_content_meta_keys`
    * **Defined:** `$tmsCourseContentMetaKeys = apply_filters('admwpp_course_content_meta_keys', $tmsCourseContentMetaKeys);`
    * **Usage:** `add_filter('admwpp_course_content_meta_keys', '[THEME_CUSTOM_METHOD]', 1, 10);`
    * **instructions**: This filter is used to set the course meta keys to be synced and concatenated to the course content, it takes as param an array of post meta keys `$tmsCourseContentMetaKeys = array('admwpp_tms_general_info', 'admwpp_tms_price_info',...);` usually those values are set in `admwpp_tms_custom_fileds_maping` custom filter defining what custom fields to sync into WordPress.


### Taxonomy Sync Filters
  * `admwpp_taxonomy_args`
    * **Defined:** `$args = apply_filters('admwpp_taxonomy_args', $args);`
    * **Usage:** `add_filter('admwpp_taxonomy_args', '[THEME_CUSTOM_METHOD]', 10, 1);`
    * **instructions**: Check [WordPress documentation](https://developer.wordpress.org/reference/functions/register_taxonomy/)

## Custom Templates

The templates used by the plugin can be found under the `templates` folder, What you might be interested in creating an override for to apply some Styling changes or changes to the HTML are the templates under the following folders:
* `/templates/course`
* `/templates/search`
* `/templates/shortcode`

To override those templates all you need to do is to create a template under the active theme folder with a similar structure and same file name:

**Example:** `/[ACTIVE_THEME_FOLDER]/admwpp/shortcode/gift-voucher.php` to override the default plugin template file `/templates/shortcode/gift-voucher.php`

**Note:** Make sure not to change or remove any Class or ID used in the template specially if its prefixed by `admwpp-` those will have default styling applied by the plugin as well as some JS events.

## Content Relations
The plugin also offers the ability to link WordPress themes generated post types to TMS locations and TMS Accounts (Partners) using Custom meta-boxes that can be activated on the Advanced Settings page.

![Metaboxes Settings](/assets/images/activate-metaboxes.png)

Meta-box to Link a Location:

![Location Metabox](/assets/images/link-tms-location.png)

Meta-box to Link a Partner:

![Partner Metabox](/assets/images/link-tms-account.png)


##  Callback Endpoints
The plugin creates two custom rest endpoint to be use during Authentication and for Webhooks callback.
you can view those endpoints under the namespace `admwpp` on: `[YOUR_SITE_URL]/wp-json/admwpp/`

### Authentication
During Authentication the plugin will be using a rest endpoint to receive a `code` to be used to generate APIs access token and refresh tokens.
**EndPoint:** `[YOUR_SITE_URL]/wp-json/admwpp/oauth/callback` 
```
register_rest_route(
    'admwpp',
    'oauth/callback',
    array(
        'methods' => 'GET',
        'callback' => array('ADM\WPPlugin\Controllers\ActivationController', 'callback'),
        'permission_callback' => '__return_true',
    )
);
```

### Webhooks
As we already know the plugin heavily relies on Webhook being triggered form the TMS to keep the content updated in WordPress.
So for this it creates an custom rest endpoint to listen to the Webhook callbacks.
**EndPoint:** `[YOUR_SITE_URL]/wp-json/admwpp/webhook/callback` 
```
register_rest_route(
    'admwpp',
    'webhook/callback',
    array(
        'methods' => 'POST',
        'callback' => array('ADM\WPPlugin\Controllers\WebhookController', 'callback'),
        'permission_callback' => '__return_true',
    )
);
```
As its a POST request being sent form TMS to the Website there is a small chance that this call might be blocked by the server configuration and might need to create an exception rule to allow those calls to happen and reach the site to be executed.

## Settings Defined Variables
The plugin has some global settings defined in the main plugin file `administrate-wp-plugin.php` those can be overridden in `wp-config.php` if needed
```
// Define the environment, set this to "Staging" to connect to Administrate staging env.
if (!defined('ADMWPP_ENV')) {
    define('ADMWPP_ENV', 'production');
}
// To load the non minified versions of the CSS and JS files
// in order to debug during development, set this to true.
if (!defined('ADMWPP_DEVELOPMENT')) {
    define('ADMWPP_DEVELOPMENT', false);
}
// Minutes to consider the portal token expired, some times you need to let plugin 
// trigger generating a weblink token before the token actually expires to you can set this to a smaller value. (min: 30, max:60)
if (!defined('ADMWPP_PORTAL_TOKEN_EXPIRY_PERIOD')) {
    define('ADMWPP_PORTAL_TOKEN_EXPIRY_PERIOD', 60);
}
```

## Plugin PHP Classes / Helper Methods
The plugin has Exposed classes methods and Helper methods available for the Developer to use on their own on the theme level, create custom search API calls, create custom filters etc....
To a certain extent those are more or less self explanatory on how to use and extend.

## Tech

* The plugin have some dependency on the [Administrate Weblink Plugin](https://github.com/Administrate/administrate-wp-weblink-plugin) for some of the Short-codes (adding Items to Cart) so in other words it always recommended to have the Administrate Weblink Plugin also installed on the WordPress site or have Weblink JS/CSS library manually Embed on the site. [Weblink Builder](https://weblink-builder.getadministrate.com/)
* The Plugin also Uses the [PHP SDK](https://github.com/Administrate/administrate-php-sdk/tags) also developed by Administrate Team. All GraphQl Queries / mutations and the authorization layer OAuth 2.0 depends on the PHP SDK.
* The [PHP SDK](https://packagist.org/packages/administrate/phpsdk) is also hosted on [Packagist](https://packagist.org/) so it can be used as stand alone.

## Potential RoadMap & Future Enhancements
  * Feature flag to enable auto injection of the corresponding Weblink Widgets on Course pages based on Course Type.
  * Pick up the filter from the catalogue page and apply those filters to Weblink Widgets on Course pages when ever is applicable.
  * Select what custom fields sync into WordPress as post metas.
  * Select what fields to inject into WordPress course content.
  * Use a drop-down to select Webhook TypeID.
  * Use checkBoxes to select the Search Active filters.
  * Select what custom fields to sync from the advances settings tab.
  * Catalog short-code builder to create custom search pages / filters / template / pager.
  * General refactoring for GraphQl Calls and Queries.
  * Improve error handling.
  * helper methods to expose content relations
  * Ability to create a selection/collection of courses to be displayed inside articles and pages as needed using short-codes.
  * Ability to select the template of a particular selection/collection of courses (list,grid,carousel,slider,…).
  * Ability to select a color code for those templates. (font color, button color,…).
  * Ability to show hide components for each particular template. (price, starting date, short-description,…).
  * Add Catalogue/Search settings params such as show/hide filters, number of courses / page , pager template (full, simple, load-more), listing template (list, grid,…).
  * Maybe add the capability to create locations post Type and synch TMS locations.
  * Maybe add the capability to create partners post Type and synch associated accounts from the TMS.
  * Maybe auto generate a location Custom Post Type and synch to TMS locations to use as location Pages on the website _(Archive page to list locations, Single location page, Related courses, etc)_
  * Maybe auto generate a Partner Custom Post Type and synch to TMS Accounts Flagged as partners to use for Partners bio page on the website _(Archive page to list partners, Single Venue page, Related courses, etc)_
  * ...

## Change log

### 1.0.0
* Setting pages to enter activation credentials and default parameters.
* Synchronization mechanism to fetch content from TMS and convert it to WordPress content.
* Expose Ability to select what custom fields to synchronize (using custom filters).
* Setup Webhook Callbacks / Configuration to handle real time updates from the TMS.
* Short-code to create a catalog page with filters and pager.
* Short-code to list the Workshops for the Learner.
* Short-code to create a list of bundled LPs with ability to add to Weblink cart. `(only applicable if client is using Bundled LPs and depends on Weblink or` [Administrate Weblink Plugin](https://github.com/Administrate/administrate-wp-weblink-plugin)).
* Short-code to create a form to add a gift voucher to Weblink cart `(depends on Weblink or` [Administrate Weblink Plugin](https://github.com/Administrate/administrate-wp-weblink-plugin)) => This short-code will be replace with a Weblink embedded widget in the future.
* Custom filters to enable the developers to run some overrides to the search queries / filters / fields / Config...
* Custom templates for the short-codes and short-code components, with ability to override on the theme level.
* Search auto compete suggestions.
* Meta-box to Link a TMS location to a WordPress Post. `(Could be an existing theme generated post for Venues info pages)`
* Meta-box to Link a TMS Account (partner) to a WordPress Post. `(Could be an existing theme generated post for Partners Bio pages)`
* ...
