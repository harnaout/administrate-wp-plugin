<?php

header('Content-Type: application/json');

require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\Course;

// $categoryId Set this value in config.php
// $weblinkActivationParams Set this value in config.php
// $return type defined in client Class 'array' -> PHP array, 'obj' -> PHP Object and 'json' -> JSON

$courseClass = new Course($weblinkActivationParams);

$keyword = "";
$fields = [];
$returnType = 'json'; //array, obj, json
$paging = ['page' => 1, 'perPage' => 25];
$sorting = ['field' => 'name', 'direction' => 'asc'];
$filters = ['categoryId' => $categoryId, 'keyword' => $keyword];

$allCourses = $courseClass->loadAll($filters, $paging, $sorting, $fields, $returnType);

print_r($allCourses);
