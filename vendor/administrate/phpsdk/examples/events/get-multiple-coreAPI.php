<?php

header('Content-Type: application/json');

require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\Event;

// $coreApiActivationParams Set this value in config.php
// $return type defined in client Class 'array' -> PHP array, 'obj' -> PHP Object and 'json' -> JSON
$args = array(
    'coreApi' => true,
);

$eventObj = new Event($coreApiActivationParams);
$events = $eventObj->loadAll($args);

print_r($events);
