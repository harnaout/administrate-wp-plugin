<?php
// --------------------------------------------------------------------
// Define Global variables For Search
// --------------------------------------------------------------------
define('ADMWPP_SEARCH_PER_PAGE', 10);
define('ADMWPP_SEARCH_DATE_FORMAT', 'mm/dd/yy');

// --------------------------------------------------------------------
// Define Global variables add a selection Block
// --------------------------------------------------------------------
define('ADMWPP_PER_PAGE', 20);

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
define('TMS_CUSTOM_PRICE_LEVEL_NAME', '*KP - 10 kinderen');

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
