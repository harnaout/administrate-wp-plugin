<?php

header('Content-Type: application/json');

require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\Category;

// $categoryId Set this value in config.php
// $weblinkActivationParams Set this value in config.php
// $return type defined in client Class 'array' -> PHP array, 'obj' -> PHP Object and 'json' -> JSON
$categoryClass = new Category($weblinkActivationParams);

$args = array(
    //'returnType' => 'json', //array, obj, json
    //'fields' => array('id','name'),
);

$category = $categoryClass->loadById($categoryId, $args);

print_r($category);
