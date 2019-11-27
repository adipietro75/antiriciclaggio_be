<?php

header("Access-Control-Allow-Origin: http://192.168.56.101:3000");
header("Access-Control-Allow-Credentials: true");

define ('PROJECT_DIRECTORY', dirname(__DIR__));

require PROJECT_DIRECTORY . '/antiriciclaggio_be/vendor/autoload.php';
require PROJECT_DIRECTORY . '/antiriciclaggio_be/config.php';

global $appConfig;

$app = new \Slim\App($appConfig['appconfig']);

include PROJECT_DIRECTORY . '/antiriciclaggio_be/src/Bootstrap/containers.php';
include PROJECT_DIRECTORY . '/antiriciclaggio_be/src/Bootstrap/routes.php';

// START !
$app->run();

?>
