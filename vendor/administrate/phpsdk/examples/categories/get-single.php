<?php

header('Content-Type: application/json');

require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\Category;

// $categoryId Set this value in config.php
// $weblinkActivationParams Set this value in config.php
// $return type defined in client Class 'array' -> PHP array, 'obj' -> PHP Object and 'json' -> JSON
$categoryClass = new Category($weblinkActivationParams);

$fields = [];
$returnType = 'json'; //array, obj, json

$category = $categoryClass->loadById($categoryId, $fields, $returnType);

print_r($category);
