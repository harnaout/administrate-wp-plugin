<?php
// --------------------------------------------------------------------
// Define Some Helpers Functions
// --------------------------------------------------------------------
//
function admwppBlank($value)
{
    return (!isset($value) || $value == null || $value == "" || $value == "null");
}

function admwppPresent($value)
{
    return !blank($value);
}

/**
* Check if object param not empty.
*
* @param  $object, The object to check.
*         $param, The object param. to check.
*
* @return Object param. value or empty string.
*
**/
function admwppObjectParam($object, $param)
{
    if (! empty($object->$param) && 'NULL' !== $object->$param && ! is_object($object->$param)) {
        return @$object->$param;
    }
    return '';
}


/**
* Function to format Number as string to float number
*
* @param  $str, The string to format / extract the float value from.
*
* @return float number.
*
**/
function admwppGetFloat($str)
{
    if (strstr($str, ",")) {
        $str = str_replace(".", "", $str); // replace dots (thousand seps) with blancs
        $str = str_replace(",", ".", $str); // replace ',' with '.'
    }

    if (preg_match("#([0-9\.]+)#", $str, $match)) {
      // search for number that may contain '.'
        $float = floatval($match[0]);
    } else {
      // take some last chances with floatval
        $float = floatval($str);
    }
    return $float;
}

function admwppIsJson($string)
{
    return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}

function admwppPrimaryLanguage()
{
    $locale = get_locale();
    if (function_exists('locale_get_primary_language')) {
        return locale_get_primary_language($locale);
    }
    $languages = explode("_", $locale);
    return $languages[0];
}
