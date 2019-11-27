<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->group('', function () {

    $this->get( '/'       , mainAppController::class . ':index' );
    $this->get( '/alive'  , mainAppController::class . ':alive' );
    $this->get( '/monitor', mainAppController::class . ':monitor' );
	$this->get( '/listaflussi', mainAppController::class . ':listaflussi' );
	$this->get( '/dettaglio', mainAppController::class . ':dettaglio' );
	$this->get( '/visualizzaric', mainAppController::class . ':visualizzaric' );
	$this->get( '/updatecloseflusso', mainAppController::class . ':updatecloseflusso' );    
	$this->get( '/updateflusso', mainAppController::class . ':updateflusso' );    
	$this->get( '/responsocreazione', mainAppController::class . ':responsocreazione' );  
	$this->get( '/cruscotto', mainAppController::class . ':cruscotto' ); 





  	
    // Main
    //$this->get( '/',      mainAppController::class . ':getUffici' );
    // Auth
    //$this->get( '/uffici[/{filter:[0-9\,]+}]', mainAppController::class . ':getUffici' );
    //$this->get( '/autorizzazioni',             mainAppController::class . ':getAutorizzazioni' );
    // Ext Module
    // $this->get( '/user',       mainAppController::class . ':getUser' );
    // $this->get( '/header',     mainAppController::class . ':getHeader' );
    // $this->get( '/footer',     mainAppController::class . ':getFooter' );
    // $this->get( '/breadcrumb', mainAppController::class . ':getBreadcrumb' );
    // Set User Ufficio and Area
    //$this->post( '/setArea',    mainAppController::class . ':setArea' );
    //$this->post( '/setUfficio', mainAppController::class . ':setUfficio' );
    // Menù
    // $this->get( '/menu[/{area:[a-z0-9_-]+}]', mainAppController::class . ':getMenu' );
    // $this->get( '/menuleft',                  mainAppController::class . ':getMenuLeft' );
    // Servizi Condivisi
    // $this->get(  '/verificasoggetto/{cf:[A-Z0-9]+}',                  sharedController::class . ':checkAUDM' );
    // $this->post( '/genera/{type:[CSV|PDF]+}/{mode:[base64|inline]+}', sharedController::class . ':docGen' );
})->add($container['AuthenticationMiddleware']);
