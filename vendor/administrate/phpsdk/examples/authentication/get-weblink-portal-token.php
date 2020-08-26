<?php

header('Content-Type: application/json');

require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\Oauth\Activator;

// $weblinkActivationParams Set this value in config.php

$activate = new Activator($weblinkActivationParams);
$response = $activate->getWeblinkPortalToken();

if ($response) {
    $portalToken = $response['body']->portal_token;
    echo $portalToken;
}
