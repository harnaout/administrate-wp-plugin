<?php
// --------------------------------------------------------------------
// Define Global variables For Search
// --------------------------------------------------------------------
define('ADMWPP_SEARCH_PER_PAGE', 12);

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
  ),
  'staging' => array(
    'label' => 'Staging',
    'administrate' => 'https://developer.stagingadministratehq.com/',
    'oauthServer' => 'https://auth.stagingadministratehq.com/oauth',
    'apiUri' => 'https://api.stagingadministratehq.com/graphql',
  ),
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
