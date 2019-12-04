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
	
    //rc01_contenzioso.inc
    $this->get('/controlla_valori1', mainAppController::class . ':controlla_valori1');
    $this->get('/controlla_valori2', mainAppController::class . ':controlla_valori2');	
    $this->get('/controlla_valori3', mainAppController::class . ':controlla_valori3'); 
    
	//rc01_model_decaduti.inc
	$this->get('/CaricaDati', mainAppController::class . ':CaricaDati');	
	
	//rc01_decadenza.inc
	
})->add($container['AuthenticationMiddleware']);
