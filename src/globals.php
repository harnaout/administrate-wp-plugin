<?php
// --------------------------------------------------------------------
// Define Global variables For GIFT Voucher
// --------------------------------------------------------------------
if (!defined('ADMWPP_MIN_VOUCHER_AMOUNT')) {
    define('ADMWPP_MIN_VOUCHER_AMOUNT', 0.01);
}
if (!defined('ADMWPP_MAX_VOUCHER_AMOUNT')) {
    define('ADMWPP_MAX_VOUCHER_AMOUNT', 250.00);
}
if (!defined('ADMWPP_VOUCHER_AMOUNT_STEP')) {
    define('ADMWPP_VOUCHER_AMOUNT_STEP', 0.01);
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
        'Please enter a valid number.'
    );
}
if (!defined('ADMWPP_VOUCHER_EMPTY_AMOUNT_MESSAGE')) {
    define(
        'ADMWPP_VOUCHER_EMPTY_AMOUNT_MESSAGE',
        'Gift voucher is below the minimum value of 0.01 %s'
    );
}
if (!defined('ADMWPP_VOUCHER_MAX_AMOUNT_MESSAGE')) {
    define(
        'ADMWPP_VOUCHER_MAX_AMOUNT_MESSAGE',
        'Gift voucher is above the maximum value of %s %s'
    );
}
if (!defined('ADMWPP_NO_WEBLINK')) {
    define('ADMWPP_NO_WEBLINK', 'Weblink not active');
}
// --------------------------------------------------------------------
// Define Global variables For Search
// --------------------------------------------------------------------
define('ADMWPP_SEARCH_PER_PAGE', 10);
define('ADMWPP_SEARCH_DATE_GQL_FORMAT', 'Y-m-d');

if (!defined('ADMWPP_SEARCH_DATE_DISPLAY_FORMAT')) {
    define('ADMWPP_SEARCH_DATE_DISPLAY_FORMAT', 'mm-dd-yy');
}



// --------------------------------------------------------------------
// Define Global variables add a selection Block
// --------------------------------------------------------------------
define('ADMWPP_PER_PAGE', 20);

// --------------------------------------------------------------------
// Define Global For Transients Keys
// --------------------------------------------------------------------
define('ADMWPP_TRANS_TMS_LOCATIONS', 'admwpp_tms_locations');
define('ADMWPP_TRANS_TMS_LC_IDS', 'admwpp_tms_categories_ids');

// --------------------------------------------------------------------
// Define Global variable To define the App Environments
// --------------------------------------------------------------------
global $ADMWPP_APP_ENVIRONMENT;
$ADMWPP_APP_ENVIRONMENT = array(
    'production' => array(
        'label' => 'Production',
        'administrate' => 'https://developer.getadministrate.com/',
        'oauthServer' => 'https://auth.getadministrate.com/oauth',
        'apiUri' => 'https://api.administrateapp.com/graphql',
        'weblink' => array(
            'oauthServer' => 'https://portal-auth.administratehq.com',
            'apiUri' => 'https://weblink-api.administratehq.com/graphql/',
        )
    ),
    'staging' => array(
        'label' => 'Staging',
        'administrate' => 'https://developer.stagingadministratehq.com/',
        'oauthServer' => 'https://auth.stagingadministratehq.com/oauth',
        'apiUri' => 'https://api.stagingadministratehq.com/graphql',
        'weblink' => array(
            'oauthServer' => 'https://portal-auth.stagingadministratehq.com',
            'apiUri' => 'https://weblink-api.stagingadministratehq.com/graphql/',
        )
    )
);

// --------------------------------------------------------------------
// Excluded Post types From check-boxes list on settings page
// --------------------------------------------------------------------
global $ADMWPP_EXCLUDED_POST_TYPES;
$ADMWPP_EXCLUDED_POST_TYPES = array(
    'attachment',
    'revision',
    'nav_menu_item',
);

// --------------------------------------------------------------------
// Define Global variables for General Settings page
// --------------------------------------------------------------------
global $ADMWPP_LANG;
$ADMWPP_LANG = array(
    'en_US'  => 'English',
    'fr_FR'  => 'French',
);

define('TMS_SHORT_DESCRIPTION_KEY', 'admwpp_tms_short_descripton');
define('TMS_LANGUAGE_KEY', 'admwpp_tms_language');
define('TMS_STICKY_POST_KEY', 'admwpp_tms_sticky_in_catalog');

// --------------------------------------------------------------------
// Define Global variables for Search Filters page
// --------------------------------------------------------------------
global $ADMWPP_SEARCH_DAYSOFWEEK;
$ADMWPP_SEARCH_DAYSOFWEEK = array(
    'Mon' => 'Monday',
    'Tue' => 'Tuesday',
    'Wed' => 'Wednesday',
    'Thu' => 'Thursday',
    'Fri' => 'Friday',
    'Sat' => 'Saturday',
    'Sun' => 'Sunday',
);

// Morning: 12am-12pm
// Afternoon: 12pm-5pm
// Evening: 5pm-12pm
// All day: An event that is >6 hours
global $ADMWPP_SEARCH_TIMEOFDAY;
$ADMWPP_SEARCH_TIMEOFDAY = array(
    'morning' => 'Morning',
    'afternoon' => 'Afternoon',
    'evening' => 'Evening',
    'allday' => 'All day',
);
