<?php

header('Content-Type: application/json');

require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\Category;

// $coreApiActivationParams Set this value in config.php
// $return type defined in client Class 'array' -> PHP array, 'obj' -> PHP Object and 'json' -> JSON
$categoryClass = new Category($coreApiActivationParams);

$args = array(
    'filters' => array(
        // array(
        //     'field' => 'name',
        //     'operation' => 'eq',
        //     'value' => 'Example Category 1',
        // )
    ),
    'paging' => array(
        'page' => 1,
        'perPage' => 2
    ),
    'sorting' => array(
        'field' => 'name',
        'direction' => 'asc'
    ),
    'returnType' => 'json', //array, obj, json
    // 'fields' => array(
    //     'id',
    //     'name'
    // ),
    'coreApi' => true,
);

$allCategories = $categoryClass->loadAll($args);

print_r($allCategories);
