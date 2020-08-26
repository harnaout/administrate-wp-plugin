<?php

header('Content-Type: application/json');

require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\Event;

// $weblinkActivationParams Set this value in config.php
// $return type defined in client Class 'array' -> PHP array, 'obj' -> PHP Object and 'json' -> JSON

$fields = [];
$returnType = 'json'; //array, obj, json
$paging = ['page' => 1, 'perPage' => 25];
$sorting = ['field' => 'title', 'direction' => 'asc'];
$filters = [];

$eventObj = new Event($weblinkActivationParams);
$events = $eventObj->loadAll($filters, $paging, $sorting, $fields, $returnType);

print_r($events);
