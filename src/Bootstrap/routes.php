<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->group('', function () {

    $this->get('/', mainAppController::class . ':index');
    $this->get('/alive', mainAppController::class . ':alive');
    $this->get('/selezionaConcessionari', mainAppController::class . ':selezionaConcessionari');
    $this->get('/getDatiTrasmessi', mainAppController::class . ':getDatiTrasmessi');
    $this->get('/getDatiTrasmessiCSV', mainAppController::class . ':getDatiTrasmessiCSV');
    $this->get('/selezioneGiochi', mainAppController::class . ':selezioneGiochi');
    $this->get('/giochiDisponibiliPerConcessione', mainAppController::class . ':giochiDisponibiliPerConcessione');
    
})->add($container['AuthenticationMiddleware']);
