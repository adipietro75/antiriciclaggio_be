<?php

header("Access-Control-Allow-Origin: http://192.168.56.101:3000");
header("Access-Control-Allow-Credentials: true");

define ('PROJECT_DIRECTORY', dirname(__DIR__));

require PROJECT_DIRECTORY . '/gestione_messaggi_607/vendor/autoload.php';
require PROJECT_DIRECTORY . '/gestione_messaggi_607/config.php';

global $appConfig;

$app = new \Slim\App($appConfig['appconfig']);

include PROJECT_DIRECTORY . '/gestione_messaggi_607/src/Bootstrap/containers.php';
include PROJECT_DIRECTORY . '/gestione_messaggi_607/src/Bootstrap/routes.php';

// START !
$app->run();

?>
