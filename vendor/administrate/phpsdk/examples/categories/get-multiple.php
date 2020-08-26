<?php

header('Content-Type: application/json');

require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\Category;

// $weblinkActivationParams Set this value in config.php
// $return type defined in client Class 'array' -> PHP array, 'obj' -> PHP Object and 'json' -> JSON
$categoryClass = new Category($weblinkActivationParams);

$fields = [];
$returnType = 'json'; //array, obj, json
$paging = ['page' => 1, 'perPage' => 25];
$sorting = ['field' => 'name', 'direction' => 'asc'];
$filters = [];

$allCategories = $categoryClass->loadAll($filters, $paging, $sorting, $fields, $returnType);

print_r($allCategories);
