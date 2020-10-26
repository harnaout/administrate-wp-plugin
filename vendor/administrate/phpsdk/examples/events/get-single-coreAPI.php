<?php

header('Content-Type: application/json');

require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\Event;

// $eventId Set this value in config.php
// $coreApiActivationParams Set this value in config.php
// $return type defined in client Class 'array' -> PHP array, 'obj' -> PHP Object and 'json' -> JSON
$args = array(
    //'returnType' => 'json', //array, obj, json
    //'fields' => array('id','name'),
    'coreApi' => true,
);

$eventObj = new Event($coreApiActivationParams);
$event = $eventObj->loadById($eventId, $args);

print_r($event);
