<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use NIM_Backend\Controllers\mainAppController;

$app->group('', function () {


    $this->get('/',                                 'mainAppController:index');
    $this->get('/alive',                            'mainAppController:alive');
    

    //RC01_antiric.inc 
    
    $this->map(['GET', 'OPTIONS', 'POST'], '/selezionaConcessionari',                'mainAppController:selezionaConcessionari');
    $this->map(['GET', 'OPTIONS', 'POST'], '/getBreadcrumb',                'mainAppController:getBreadcrumb');
    $this->map(['GET', 'OPTIONS', 'POST'], '/getMenuLeft',                'mainAppController:getMenuLeft');

    
    $this->map(['GET', 'OPTIONS', 'POST'], '/getMokups',                'mainAppController:getMokups');
    $this->get('/getDatiTrasmessi',                 'mainAppController:getDatiTrasmessi');
    $this->get('/getDatiTrasmessiCSV',              'mainAppController:getDatiTrasmessiCSV');
    $this->get('/selezioneGiochi',                  'mainAppController:selezioneGiochi');
    $this->get('/giochiDisponibiliPerConcessione',  'mainAppController:giochiDisponibiliPerConcessione');

    //RC01_antiric_monitoraggio.inc 
    $this->get('/getForm',                          'mainAppController:getForm');
    $this->get('/getResult',                        'mainAppController:getResult');
    $this->get('/checkInput',                       'mainAppController:checkInput');
    $this->get('/calcolaAnno',                      'mainAppController:calcolaAnno');
    $this->get('/getCSV',                           'mainAppController:getCSV');
    $this->get('/getResult1',                       'mainAppController:getResult1');
    $this->get('/getCSV1',                          'mainAppController:getCSV1');
    $this->get('/getResult1det',                    'mainAppController:getResult1det');
    $this->get('/getCSV1Dett',                      'mainAppController:getCSV1Dett');
    $this->get('/getResultNew',                     'mainAppController:getResultNew');
    $this->get('/getCSVNew',                        'mainAppController:getCSVNew');

    //antiric_XLS_elenco_societa.inc 
    $this->get('/antiric_XLS_elenco_societa',       'mainAppController:antiric_XLS_elenco_societa');

    //antiric_XLS_elenco_operazioni.inc 
    $this->get('/antiric_XLS_elenco_operazioni',    'mainAppController:antiric_XLS_elenco_operazioni');
    
    //antiric_deroga.inc 
    $this->get('/getFormDeroga',    'mainAppController:getFormDeroga');
    $this->get('/getFormMon',       'mainAppController:getFormMon');
    $this->get('/putFormMon',    'mainAppController:putFormMon');

})->add($container['AuthenticationMiddleware']);
