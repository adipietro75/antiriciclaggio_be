<?php

require_once '/opt/web/sys/hessian/HessianClient.php';

/**
 * @name string
 */
class Enti {

	public $name = '';

}

/**
 * @codiceAOO string
 * @registro string
 * @dataProtocollo string
 * @modalita string
 * @idTransazione NULL
 * @ut string
 * @mittDest string
 * @oggetto string
 * @servizio string
 * @ente Enti
 */
class Protocolla {

	public $codiceAOO = '';
	public $registro = '';
	public $dataProtocollo = '';
	public $modalita = '';
	public $idTransazione = '';
	public $ut = '';
	public $mittDest = '';
	public $oggetto = '';
	public $servizio = '';
	public $ente = '';

}

/**
 * @segnaturaProtocollo string
 */
class ProtocollaResponse {

	public $segnaturaProtocollo = '';

}

class ProtocollaClient {

	const SERVIZIO = 'P2JSM_ProtocollazioneValidazione';
	const URL = 'http://127.0.0.1:8080/P2JSM_ProtocollazioneValidazione/home';
	const USERNAME_TOKEN = 'USERNAME_TOKEN';

	public $proxy = '';

	public function __construct() {
		$options = new HessianOptions();
		$options->typeMap['Enti']               = 'it.sogei.wssecurity.mediator.webService.types.Enti';
		$options->typeMap['Protocolla']         = 'it.sogei.wssecurity.mediator.webService.types.Protocolla';
		$options->typeMap['ProtocollaResponse'] = 'it.sogei.wssecurity.mediator.webService.types.ProtocollaResponse';
		$this->proxy = new HessianClient(self::URL, $options);
		
	}

	public function protocolla($codiceAoo, $registro,$ente, $dataProtocollo, $modalita, $idTransazione, $ut, $mittDest, $oggetto, $servizio) {
		try {
			//echo sprintf("Try to call service: %s, with method: %s ...\n <br />", self::SERVIZIO, __FUNCTION__);

			$enteObj = new Enti();
			//$ente->name = 'AAMS';
			$enteObj->name = $ente;
            
			$protocolla = new Protocolla();
			$protocolla->codiceAOO = (int) $codiceAoo;
			$protocolla->registro = (int) $registro;
			$protocolla->dataProtocollo = $dataProtocollo;
			$protocolla->modalita = $modalita;
			$protocolla->idTransazione = $idTransazione;
			$protocolla->ut = $ut;
			$protocolla->mittDest = $mittDest;
			$protocolla->oggetto = $oggetto;
			$protocolla->servizio = $servizio;
			$protocolla->ente = $enteObj;
			
			$res = $this->proxy->protocolla($protocolla);
			
			return $res;
		} catch (Exception $excp) {
			throw $excp;
		}
	}

}
