<?php

header('Content-Type: application/json');

require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\LearningPath;

// $coreApiActivationParams Set this value in config.php
// $return type defined in client Class 'array' -> PHP array, 'obj' -> PHP Object and 'json' -> JSON
$learningPathClass = new LearningPath($coreApiActivationParams);

$args = array(
    'filters' => array(
        // array(
        //     'field' => 'name',
        //     'operation' => 'eq',
        //     'value' => 'First test learning path',
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
    //     'name',
    //     'learningObjectives' => array(
    //         'pageInfo' => array(
    //             'totalRecords'
    //         )
    //     ),
    // ),
    'coreApi' => true,
);

$result = $learningPathClass->loadAll($args);

print_r($result);
