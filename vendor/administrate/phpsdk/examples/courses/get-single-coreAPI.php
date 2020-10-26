<?php

header('Content-Type: application/json');

require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\Course;

// $courseId defined in config.php
// $coreApiActivationParams Set this value in config.php
// $return type defined in client Class 'array' -> PHP array, 'obj' -> PHP Object and 'json' -> JSON
$courseClass = new Course($coreApiActivationParams);

$args = array(
    //'returnType' => 'json', //array, obj, json
    //'fields' => array('id','name'),
    'coreApi' => true,
);

$course = $courseClass->loadById($courseId, $args);

print_r($course);
