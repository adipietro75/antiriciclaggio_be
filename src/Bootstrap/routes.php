<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use NIM_Backend\Controllers\mainAppController;

$app->group('', function () {


    $this->map(['GET', 'OPTIONS', 'POST'], '/',                                 'mainAppController:index');
    $this->map(['GET', 'OPTIONS', 'POST'], '/alive',                            'mainAppController:alive');
    

    //RC01_antiric.inc 
    
    $this->map(['GET', 'OPTIONS', 'POST'], '/selezionaConcessionari',                'mainAppController:selezionaConcessionari');
    $this->map(['GET', 'OPTIONS', 'POST'], '/getBreadcrumb',                'mainAppController:getBreadcrumb');
    $this->map(['GET', 'OPTIONS', 'POST'], '/getMenuLeft',                'mainAppController:getMenuLeft');
    $this->map(['GET', 'OPTIONS', 'POST'], '/getDatiTrasmessi',                'mainAppController:getDatiTrasmessi');
    $this->map(['GET', 'OPTIONS', 'POST'], '/getMokups',                'mainAppController:getMokups');

    
    $this->map(['GET', 'OPTIONS', 'POST'], '/getDatiTrasmessiCSV',              'mainAppController:getDatiTrasmessiCSV');
    $this->map(['GET', 'OPTIONS', 'POST'], '/selezioneGiochi',                  'mainAppController:selezioneGiochi');
    $this->map(['GET', 'OPTIONS', 'POST'], '/giochiDisponibiliPerConcessione',  'mainAppController:giochiDisponibiliPerConcessione');

    //RC01_antiric_monitoraggio.inc 
    $this->map(['GET', 'OPTIONS', 'POST'], '/getForm',                          'mainAppController:getForm');
    $this->map(['GET', 'OPTIONS', 'POST'], '/getResult',                        'mainAppController:getResult');
    $this->map(['GET', 'OPTIONS', 'POST'], '/checkInput',                       'mainAppController:checkInput');
    $this->map(['GET', 'OPTIONS', 'POST'], '/calcolaAnno',                      'mainAppController:calcolaAnno');
    $this->map(['GET', 'OPTIONS', 'POST'], '/getCSV',                           'mainAppController:getCSV');
    $this->map(['GET', 'OPTIONS', 'POST'], '/getResult1',                       'mainAppController:getResult1');
    $this->map(['GET', 'OPTIONS', 'POST'], '/getCSV1',                          'mainAppController:getCSV1');
    $this->map(['GET', 'OPTIONS', 'POST'], '/getResult1det',                    'mainAppController:getResult1det');
    $this->map(['GET', 'OPTIONS', 'POST'], '/getCSV1Dett',                      'mainAppController:getCSV1Dett');
    $this->map(['GET', 'OPTIONS', 'POST'], '/getResultNew',                     'mainAppController:getResultNew');
    $this->map(['GET', 'OPTIONS', 'POST'], '/getCSVNew',                        'mainAppController:getCSVNew');

    //antiric_XLS_elenco_societa.inc 
    $this->map(['GET', 'OPTIONS', 'POST'], '/antiric_XLS_elenco_societa',       'mainAppController:antiric_XLS_elenco_societa');

    //antiric_XLS_elenco_operazioni.inc 
    $this->map(['GET', 'OPTIONS', 'POST'], '/antiric_XLS_elenco_operazioni',    'mainAppController:antiric_XLS_elenco_operazioni');
    
    //antiric_deroga.inc 
    $this->map(['GET', 'OPTIONS', 'POST'], '/getFormDeroga',    'mainAppController:getFormDeroga');
    $this->map(['GET', 'OPTIONS', 'POST'], '/getFormMon',       'mainAppController:getFormMon');
    $this->map(['GET', 'OPTIONS', 'POST'], '/putFormMon',    'mainAppController:putFormMon');

})->add($container['AuthenticationMiddleware']);
