<?php
require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\Oauth\Activator;

//$coreActivationParams defined in config.php

$activate = new Activator($coreApiActivationParams);
//$response = $activate->handleAuthorizeCallback($_GET);
echo "Add this code in config file in order to get access_token and refesh_token";
echo "<pre>";
echo json_encode(array('authorization_code'=>$_GET['code']));
echo "</pre>";
