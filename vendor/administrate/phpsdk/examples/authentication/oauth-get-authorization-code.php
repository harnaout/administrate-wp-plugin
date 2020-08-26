<?php

require_once '../config.php';
require_once '../../vendor/autoload.php';

use Administrate\PhpSdk\Oauth\Activator;

//$coreApiActivationParams is set in config.php

$activationObj = new Activator($coreApiActivationParams);

echo "<a href='" . $activationObj->getAuthorizeUrl() . "'>GET Authorization Code<a/><br>";
