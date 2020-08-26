<?php

header('Content-Type: application/json');

require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\Event;

// $courseCode Set this value in config.php
// $weblinkActivationParams Set this value in config.php
// $return type defined in client Class 'array' -> PHP array, 'obj' -> PHP Object and 'json' -> JSON

$fields = [];
$returnType = 'json'; //array, obj, json
$paging = ['page' => 1, 'perPage' => 25];
$sorting = ['field' => 'title', 'direction' => 'asc'];
$filters = ['courseCode' => $courseCode];

$eventObj = new Event($weblinkActivationParams);
$events = $eventObj->loadByCourseCode($filters, $paging, $sorting, $fields, $returnType);

print_r($events);
