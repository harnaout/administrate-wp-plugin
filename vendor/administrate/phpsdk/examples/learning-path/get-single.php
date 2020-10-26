<?php

header('Content-Type: application/json');

require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\LearningPath;

// $categoryId Set this value in config.php
// $weblinkActivationParams Set this value in config.php
// $return type defined in client Class 'array' -> PHP array, 'obj' -> PHP Object and 'json' -> JSON
$learningPathObj = new LearningPath($weblinkActivationParams);

$args = array(
    //'returnType' => 'json', //array, obj, json
    //'fields' => array('id','name'),
);

$learningPath = $learningPathObj->loadById($learningPathId, $args);

print_r($learningPath);
