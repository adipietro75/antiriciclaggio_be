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

    //rc01_contenzioso.inc
    $this->get('/controlla_valori1', 'mainAppController:controlla_valori1');
    $this->get('/controlla_valori2', 'mainAppController:controlla_valori2');
    $this->get('/controlla_valori3', 'mainAppController:controlla_valori3');

    //rc01_model_decaduti.inc
    $this->get('/CaricaDati', 'mainAppController:CaricaDati');

    //rc01_decadenza.inc

})->add($container['AuthenticationMiddleware']);
