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
        'perPage' => 50
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
    'fields' => array(
        'id',
        'name',
        'learningObjectives' => array(
            'type' => 'edges',
            'fields' => array(
                'id',
                '__typename',
                '... on CourseObjective' => array(
                    'courseTemplate' => array(
                        'id',
                        'events' => array(
                            'type' => 'edges',
                            'filtersType' => 'EventFieldGraphFilter',
                            'filters' => array(
                                array(
                                    'field' => 'status',
                                    'operation' => 'eq',
                                    'value' => 'Active'
                                ),
                            ),
                            'fields' => array(
                                'id',
                                'title',
                                'status',
                                'location' => array('id', 'name')
                            )
                        )
                    )
                )
            )
        ),
    )
);

$result = $learningPathClass->loadAll($args);

print_r($result);
