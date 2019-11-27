<?php

require_once '/opt/web/sys/hessian/HessianClient.php';

class SoapMessageDto
{
    public $cfPiva;
    public $idServizio;
    public $isPOI;
}

class GetInfoSoggettoFiscale
{
    public $msg;
}

class GetInfoSoggettoFiscaleResponse
{
    public $_return;
}

class servizioWebAUDM
{
    const MEDIATOR_NAME  = 'P2JSM_ConsultazioneAnagraficaMonopoli';
    const MEDIATOR_URL   = 'http://127.0.0.1:8080/P2JSM_ConsultazioneAnagraficaMonopoli/home';
    const USER_TOKEN     = 'USERNAME_TOKEN';

    public $proxy;

    public function __construct()
    {
        $options = new HessianOptions();

        $options->typeMap['SoapMessageDto']                 = 'it.sogei.wssecurity.mediator.webService.types.SoapMessageDto';
        $options->typeMap['GetInfoSoggettoFiscale']         = 'it.sogei.wssecurity.mediator.webService.types.GetInfoSoggettoFiscale';
        $options->typeMap['GetInfoSoggettoFiscaleResponse'] = 'it.sogei.wssecurity.mediator.webService.types.GetInfoSoggettoFiscaleResponse';

        $this->proxy = new HessianClient(self::MEDIATOR_URL, $options);
    }

    public function getInfoSoggettoFiscale($cf, $idServizio=6, $isPOI=1)
    {
        try {
            $arg1 = new SoapMessageDto();
            $arg1->cfPiva     = strtoupper($cf); // C.F. da Verificare
            $arg1->idServizio = $idServizio;     // Potrebbe essere diverso.
            $arg1->isPOI      = $isPOI;          // Parametro opzionale:  0 = soggetto non di interesse; 1 = soggetto di interesse

            $arg0 = new GetInfoSoggettoFiscale();
            $arg0->msg = $arg1;
            $risultato = $this->proxy->getInfoSoggettoFiscale($arg0, true, self::USER_TOKEN);

            return $risultato;
        }catch(Exception $excp){
            throw $excp;
        }
    }
}

?>