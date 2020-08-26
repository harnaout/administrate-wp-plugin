<?php
require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\Oauth\Activator;

//$coreActivationParams defined in config.php
//$authorizationCode defined in config.php

$activationObj = new Activator($coreApiActivationParams);
$response = $activationObj->handleAuthorizeCallback(array( 'code' => $authorizationCode ));

if ($response) {
    $accessToken = $response['body']->access_token;
    $refreshToken = $response['body']->refresh_token;

    echo "<pre>";
    print_r(array(
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken
    ));
    echo "</pre>";

    $refreshLink =  BASE_URL . "/examples/authentication/oauth-refresh-tokens.php";
    $refreshLink .= "?token=$refreshToken";

    echo "<a href='$refreshLink' target='_blank'>Refresh token<a/>";
}
