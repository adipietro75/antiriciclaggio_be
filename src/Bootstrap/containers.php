<?php

use NIM_Backend\Controllers AS Controllers;
use NIM_Backend\Models AS Models;
use NIM_Backend\Middleware As Middleware;

use NIM_Backend\SharedLibs As SharedLibs;

$container = $app->getContainer();

$container['logger'] = function($c) {
    $today  = date('Ymd');
    $logger = new \Monolog\Logger('NIM_Logger');
    $file_handler = new \Monolog\Handler\StreamHandler(sprintf('%s/antiriciclaggio/logs/debug_'.$today.'.log', PROJECT_DIRECTORY));
    $logger->pushHandler($file_handler);

    return $logger;
};

$container['DBManager'] = function($c) {
    return new SharedLibs\DBManager($c->get('logger'));
};

$container['DaoFactory'] = function($c) {
    return new Models\DaoFactory($c->get('logger'), $c->get('DBManager'));
};

$container['AuthenticationMiddleware'] = function($c) {
    return new Middleware\AuthenticationMiddleware($c->get('logger'));
};

$container['mainAppController'] = function($c) {
    return new Controllers\mainAppController($c->get('logger'), $c->get("DaoFactory"));
};

// Servizi Condivisi
$container['sharedController'] = function($c) {
    return new Controllers\sharedController($c->get('logger'), $c->get("DaoFactory"));
};

// Error Handlers
if ($appConfig['debug'] !== true) {
    $container['notFoundHandler'] = function ($c) {
        return function ($request, $response) use ($c) {
            $errorArray = ["status" => "NOK", "message" => "NOT_FOUND"];
            return $response->withStatus(404)->withHeader('Content-Type','application/json')->write(json_encode($errorArray));
        };
    };

    $container['errorHandler'] = function ($c) {
        return function ($request, $response) use ($c) {
            $errorArray = ["status" => "NOK", "message" => "SOMETHING_WENT_WRONG"];
            return $response->withStatus(500)->withHeader('Content-Type','application/json')->write(json_encode($errorArray));
        };
    };
}

?>