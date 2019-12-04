<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use NIM_Backend\Controllers\mainAppController;

$app->group('', function () {

    $this->get('/',                                 'mainAppController:index');
    $this->get('/alive',                            'mainAppController:alive');
    $this->get('/selezionaConcessionari',           'mainAppController:selezionaConcessionari');
    $this->get('/getDatiTrasmessi',                 'mainAppController:getDatiTrasmessi');
    $this->get('/getDatiTrasmessiCSV',              'mainAppController:getDatiTrasmessiCSV');
    $this->get('/selezioneGiochi',                  'mainAppController:selezioneGiochi');
    $this->get('/giochiDisponibiliPerConcessione',  'mainAppController:giochiDisponibiliPerConcessione');

})->add($container['AuthenticationMiddleware']);
