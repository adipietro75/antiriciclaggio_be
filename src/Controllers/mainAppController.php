<?php

namespace NIM_Backend\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class mainAppController
{
    protected $logger;
    protected $db;

    public function __construct(\Monolog\Logger $logger, $db)
    {
        global $appConfig;

        $this->db     = $db;
        $this->logger = $logger;
        $this->config = $appConfig;

        session_start();

        $_SESSION["nome_utente"] = "Fabrizio Santini";
        $_SESSION["cf_utente"]   = "SNTFRZ73T16H501R";
        $_SESSION["ufficio"]     = "Direzione Generale";
        $_SESSION["isp"]         = "00100";
        $_SESSION["cod_isp"]     = "0";
    }

    function __destruct()
    {
        session_destroy();
    }

    public function index(Request $request, Response $response)
    {
        $data = ["status" => "OK", "result" => $this->config['defres']];
        return $response->withJson($data, 200);
    }

    public function alive(Request $request, Response $response)
    {
        $data = ["status" => "OK", "result" => $_SESSION];
        return $response->withJson($data, 200);
    }

    public function monitor(Request $request, Response $response) //, $args)
    {
        // if (isset($args['filter'])) {
        //     $stringa = urldecode($args['filter']);
        // } else {
        //     $stringa = "";
        // }

        $request_data = $request->getParsedBody();
        $application    = "Gestione messaggi 607 - Monitor";
        $COD_MITT       = $request_data['COD_MITT'];
        $COD_PROD       = $request_data['COD_PROD'];
        $P_PK_SG        = $request_data['P_PK_SG'];
        $stato_flusso   = $request_data['stato_flusso'];

        $flgDisabledProd = "disabled";
        $flgDisabledSg   = "disabled";


        $selectConcessionari   = $this->db->retrieveListConc();

        $selectProduttori   = $this->db->retrieve_prod($COD_MITT);

        $selectSistemiDiGioco   =  $this->db->retrieveSg($COD_MITT, $COD_PROD);  //$db->retrieveSg($COD_MITT,$COD_PROD);

        $selectStati[0]['ID'] = "";
        $selectStati[0]['DENOMINAZIONE'] = "Tutti";
        $selectStati[1]['ID'] = "C";
        $selectStati[1]['DENOMINAZIONE'] = "Chiusi";
        $selectStati[2]['ID'] = "O";
        $selectStati[2]['DENOMINAZIONE'] = "Aperti";


        //HACK ALE -- COMPONENTE CHE STA SUL REPOSITORY CENTRALE E NON ALL'INTERNO DI OGNI APPLICAZIONE
        // NAVIGATION BAR
        //$navbar = new NavigationBar();
        //$navbar->add_item("Gestione messaggi 607", "/php/videogiochi/gestione_messaggi_607/index.php", "Gestione messaggi 607");	   
        //$navbar->add_item("Monitor", "", "Monitor");
        //$response_data['navbarArray              = $navbar->ShowBar();
        $navbarArray[0] = ["Gestione messaggi 607", "/php/videogiochi/gestione_messaggi_607/index.php", "Gestione messaggi 607"];
        $navbarArray[1] = ["Monitor", "", "Monitor"];
        //-----------------------------------

        $response_data = array();
        $response_data['error']                 = false;
        $response_data['navbarArray']           = $navbarArray;
        $response_data['selectConcessionari']   = $selectConcessionari;
        $response_data['selectProduttori']      = $selectProduttori;
        $response_data['selectSistemiDiGioco']  = $selectSistemiDiGioco;
        $response_data['selectStati']           = $selectStati;
        $response_data['application']           = $application;
        $response_data['flgDisabledProd']       = $flgDisabledProd;
        $response_data['flgDisabledSg']         = $flgDisabledSg;
        $response->write(json_encode($response_data));

        if (is_array($response_data)) {
            $data = ["status" => "OK", "result" => $response_data];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero del monitoring"];
            return $response->withJson($data, 500);
        }
    }
	
	public function listaflussi(Request $request, Response $response)
    {
		$request_data = $request->getParsedBody();
        $COD_MITT       = $request_data['COD_MITT'];
        $COD_PROD       = $request_data['COD_PROD'];
        $P_PK_SG        = $request_data['P_PK_SG'];
        $stato_flusso   = $request_data['stato_flusso'];
       	$application    = "Gestione messaggi 607 - Lista Flussi";
	    $comeBackAction = $request_data['comeBackAction'];
		
		
		$listaFlussi   = $this->db->retrieveFlussi($COD_MITT, $COD_PROD, $P_PK_SG, $statoFlusso);
		
       // NAVIGATION BAR
        //$navbar = new NavigationBar();
        //$navbar->add_item("Gestione messaggi 607", "/php/videogiochi/gestione_messaggi_607/index.php", "Gestione messaggi 607");	   
        //$navbar->add_item("Monitor", "", "Monitor");
        //$response_data['navbarArray              = $navbar->ShowBar();
		$navbar->add_item("Gestione messaggi 607", "/php/videogiochi/gestione_messaggi_607/index.php", "Gestione messaggi 607");	   
		$navbar->add_item("Lista Flussi", "", "Lista Flussi");
        //-----------------------------------

        $response_data = array();
        $response_data['error']                 = false;
        $response_data['navbarArray']           = $navbarArray;
		$response_data['COD_MITT']           = $COD_MITT;
		$response_data['COD_PROD']           = $COD_PROD;
		$response_data['P_PK_SG']           = $P_PK_SG;
		$response_data['stato_flusso']           = $stato_flusso;
		$response_data['comeBackAction']           = $comeBackAction;  
        $response_data['listaflussi']   		= $listaFlussi;
        $response_data['application']           = $application;
 

        $response->write(json_encode($response_data));

        if (is_array($response_data)) {
            $data = ["status" => "OK", "result" => $response_data];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero della lista flussi"];
            return $response->withJson($data, 500);
        }
    }

	public function dettaglio(Request $request, Response $response)
    {
		$request_data = $request->getParsedBody();
        $p_pk_607_flusso       = $request_data['p_pk_607_flusso'];
        $msgerr       = $request_data['msgerr'];
        $msgEsitoUpdateClose        = $request_data['msgEsitoUpdateClose'];
        $stato_flusso   = $request_data['stato_flusso'];
       	$application    = "Gestione messaggi 607 - Dettaglio";
	    $comeBackAction = $request_data['comeBackAction'];
		$COD_MITT       = $request_data['COD_MITT'];
        $COD_PROD       = $request_data['COD_PROD'];
        $P_PK_SG        = $request_data['P_PK_SG'];
		$esitoModifica  = $request_data['esitoModifica'];

		if($comeBackAction=='cruscotto'){
			$comeBackAction = "cruscotto";
		}
		else
		{
			$comeBackAction = "listaflussi";
		}

		$resultFlusso   = $this->db->retrieveFlussi($p_pk_607_flusso);
		$statoDelFlusso     = $resultFlusso[0]['STATO'];

		//indica se sono visibili le griglie dei dettagli invio messaggi
		$flgGriglieVisibili                     = 1;

		if($statoDelFlusso == 3 ||
				$statoDelFlusso == 4){
			$flgGriglieVisibili                     = 0;
		}
		if($flgGriglieVisibili){

			/* messaggi 607 inviati ed accettati */

			$result607ok = $this->db->retrieve_607_ok($p_pk_607_flusso);
			//if($model->getError()){
			//	$msgerr                    = $model->getError();
			//}

			$num_mess_ok = count($result607ok);

			/* messaggi 607 inviati ed accettati */

			/* messaggi 607 inviati e scartati */

			$result607nok = $this->db->retrieve_607_nok($p_pk_607_flusso);
			//if($model->getError()){
			//	$msgerr                    = $model->getError();
			//}

			$num_mess_nok = count($result607nok);

			/* messaggi 607 inviati e scartati */

			/* messaggi 607 annullati per tipo periodo e tipologia componente */

			$result607annullatiGrid = $this->db->retrieve_num_600_annullati($p_pk_607_flusso);
			//if($model->getError()){
			//	$msgerr                    = $model->getError();
			//}


			/* messaggi 607 annullati per tipo periodo e tipologia componente */

			/* messaggi 607 ritrasmessi e accettati */

			$result607ritrasmAccGrid = $this->db->retrieve_num_600_ritrasm($p_pk_607_flusso);
			//if($model->getError()){
			//	$msgerr                    = $model->getError();
			//}



			/* messaggi 607 ritrasmessi e accettati */

			/* messaggi 607 annullati e non ritrasmessi */

			$result607annNonRitrasmGrid = $this->db->retrieve_num_600_non_ritrasm($p_pk_607_flusso);
			//if($model->getError()){
			//	$msgerr                    = $model->getError();
			//}
		}

		//la variabile serve per vedere se ci sono dei messaggi annullati non ritrasmessi
		// è minore o maggiore di 0
		$flgNonRistrasmessi = 0;

		if(!empty($result607annNonRitrasm)){
			foreach ($result607annNonRitrasm as $message){
				if($message['CNT']>0){
					$flgNonRistrasmessi=1;
				}
			}
		}

		$numeroTotale607  = $resultFlusso[0]['NUMERO_TOTALE_607'];

		$dataFineInvio607 = $resultFlusso[0]['DATA_FINE_INVIO607'];
		$exploded = explode('/', $dataFineInvio607);
		$dataFineInvio607 = $exploded[2]."/".$exploded[1]."/".$exploded[0];

		$dataFineInvio607Timestamp= strtotime($dataFineInvio607); 

		/* messaggi 607 annullati e non ritrasmessi */

		$dataFineRitr600 = $resultFlusso[0]['DATA_RITRASM_600'];

		$exploded = explode('/', $dataFineRitr600);

		$dataFineRitr600 = $exploded[2]."/".$exploded[1]."/".$exploded[0];

		//timestamp della data odierna
		$today = date('Y/m/d');
		$todayTimestamp = strtotime($today);

		//timestamp della data fine ritrasmissione
		$dataFineRitr600Timestamp = strtotime($dataFineRitr600); 

		//rende modificabile o meno il flusso
		$flgReadOnly = 0;

		if($statoDelFlusso > 0){
			$flgReadOnly = 1;
		}

		// situazione tutti e tre pulsandi disabilitati
		$flgEnabledButtonRipristinoBD           = "disabled";
		$flgEnabledButtonChiudiFlussoReinvia    = "disabled";
		$flgEnabledButtonChiudiFlusso           = "disabled";

		// verifica condizione flusso aperto
		if(empty($statoDelFlusso)){
			if($todayTimestamp>$dataFineInvio607Timestamp){
				if($num_mess_ok != $numeroTotale607){
					$flgEnabledButtonRipristinoBD = "";

				}

			}

			if($todayTimestamp>$dataFineRitr600Timestamp){

				$flgEnabledButtonRipristinoBD = "";

				if($flgNonRistrasmessi>0){
					$flgEnabledButtonChiudiFlussoReinvia = "";
				}else{
					$flgEnabledButtonChiudiFlusso = "";
				}

			}

		}
		
		$statoDelFlussoString = "APERTO";

		switch ($statoDelFlusso){

			case 1:
				$statoDelFlussoString = "CHIUSO - reinvio parziale";
				break;

			case 2:
				$statoDelFlussoString = "CHIUSO - reinvio completato";
				break;

			case 3:
				$statoDelFlussoString = "CHIUSO - ripristino banca dati richesto";
				break;

			case 4:
				$statoDelFlussoString = "CHIUSO - rispristino banca dati eseguito";
				break;

		};
		
		
		$cntAnn             = 0;
		$cntRitrAcc         = 0;
		$cntAnnNonRitr      = 0;

		if(!empty($result607annullati)){
			foreach ($result607annullati as $item){
				$cntAnn = $cntAnn+$item['CNT'];
			}
		}

		if(!empty($result607ritrasmAcc)){
			foreach ($result607ritrasmAcc as $item){
				$cntRitrAcc = $cntRitrAcc+$item['CNT'];
			}
		}

		if(!empty($result607annNonRitrasm)){
			foreach ($result607annNonRitrasm as $item){
				$cntAnnNonRitr = $cntAnnNonRitr+$item['CNT'];
			}
		}


       // NAVIGATION BAR
        //$navbar = new NavigationBar();
        //$navbar->add_item("Gestione messaggi 607", "/php/videogiochi/gestione_messaggi_607/index.php", "Gestione messaggi 607");	   
        //$navbar->add_item("Monitor", "", "Monitor");
        //$response_data['navbarArray              = $navbar->ShowBar();
		$navbar->add_item("Gestione messaggi 607", "/php/videogiochi/gestione_messaggi_607/index.php", "Gestione messaggi 607");	   
		$navbar->add_item("Lista Flussi", "", "Dettaglio");
        //-----------------------------------

        $response_data = array();
        $response_data['error']                 = false;
        $response_data['navbarArray']           = $navbarArray;
		$response_data['esitoModifica']           	= $esitoModifica;
		$response_data['flgEnabledButtonRipristinoBD']           	= $flgEnabledButtonRipristinoBD;
		$response_data['flgEnabledButtonChiudiFlusso']           	= $flgEnabledButtonChiudiFlusso;
		$response_data['flgEnabledButtonChiudiFlussoReinvia']          = $flgEnabledButtonChiudiFlussoReinvia;
		$response_data['flgVisibleButtonModifica']        = $flgVisibleButtonModifica;  
		$response_data['flgGriglieVisibili']        = $flgGriglieVisibili;  
		$response_data['p_pk_607_flusso']        = $p_pk_607_flusso;  
		$response_data['dettaglioFlusso']        = $resultFlusso[0];
		$response_data['flgVisibleButtonModifica']        = $flgVisibleButtonModifica;  
        $response_data['application']           = $application;
		$response_data['num_mess_ok']           = $num_mess_ok;
		$response_data['num_mess_nok']           = $num_mess_nok;
		$response_data['result607annullatiGrid']           = $result607annullatiGrid;
		$response_data['result607ritrasmAccGrid']           = $result607ritrasmAccGrid;	
		$response_data['result607annNonRitrasmGrid']           = $result607annNonRitrasmGrid;
		$response_data['statoDelFlusso']           = $statoDelFlusso;	
		$response_data['statoDelFlussoString']           = $statoDelFlussoString;
		$response_data['COD_MITT']           = $COD_MITT;
		$response_data['COD_PROD']           = $COD_PROD;
		$response_data['P_PK_SG']           = $P_PK_SG;
		$response_data['statoFlusso']           = $statoFlusso;
		$response_data['comeBackAction']           = $comeBackAction;


        $response->write(json_encode($response_data));

        if (is_array($response_data)) {
            $data = ["status" => "OK", "result" => $response_data];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero del dettaglio"];
            return $response->withJson($data, 500);
        }
    }

  	public function updatecloseflusso(Request $request, Response $response)
    {
		$request_data = $request->getParsedBody();
        $p_pk_607_flusso       = $request_data['p_pk_607_flusso'];
	    $p_stato      		   = $request_data['p_stato'];

		$esitoUpdateClose       = $this->db->updateCloseFlusso($p_pk_607_flusso, $p_stato);		
		
		if($esitoUpdateClose==1 && $p_stato==3){
			$msgEsitoUpdateClose = "E' stato avviato il processo di ripristino della banca dati."
				. "Controllare sul cruscotto l'avvenuta esecuzione del processo di ripristino richiesto.";
		}

		if($esitoUpdateClose==1){
			$model->db_commit();
		}else{
			$model->db_rollback();
			$msgerr = "Attenzione! Si &eacute; verificato un errore durante l&rsquo;update del flusso, contattare l&rsquo;amministratore del sistema.";
		}

		$response_data = array();
 		$response_data['msgerr']                = $msgerr;
		$response_data['p_pk_607_flusso']       = $p_pk_607_flusso;
        $response_data['msgEsitoUpdateClose']   = $msgEsitoUpdateClose;
		$response_data['action_forward']          	= 'dettaglio';

        $response->write(json_encode($response_data));

        if (is_array($response_data)) {
            $data = ["status" => "OK", "result" => $response_data];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero della chiusura del flusso"];
            return $response->withJson($data, 500);
        }
    }  

  	public function updateflusso(Request $request, Response $response)
    {
		$request_data		   = $request->getParsedBody();
        $p_pk_607_flusso       = $request_data['p_pk_607_flusso'];
	    $p_data_fine_invio607  = $request_data['p_data_fine_invio607'];
	    $p_numero_totale_607   = $request_data['p_numero_totale_607'];
	    $p_data_ritrasm_600    = $request_data['p_data_ritrasm_600'];
	    $p_data_fine_invio607  = $request_data['p_data_replica'];

		$COD_MITT       = $request_data['COD_MITT'];
		$COD_PROD       = $request_data['COD_PROD'];
		$P_PK_SG        = $request_data['P_PK_SG'];
		$statoFlusso    = $request_data['stato_flusso'];		
		
		$numero_totale_607_result  = $this->db->verifyExtendFlusso($p_pk_607_flusso, $p_numero_totale_607);

		$ERRCODE = $numero_totale_607_result['ERRCODE'];
		$ERRTEXT = $numero_totale_607_result['ERRTEXT'];

		$msgerr = null;
		if($ERRCODE==0){

			$esito = $this->db->update_extend_flusso($p_pk_607_flusso,$p_data_fine_invio607,$p_numero_totale_607,$p_data_ritrasm_600,$p_data_replica);

			if($esito==1){
				$msgerr = "Attenzione! Si &eacute; verificato un errore durante l&rsquo;update del flusso, contattare l&rsquo;amministratore del sistema.";
				$model->db_rollback();
			}elseif($esito==0){
				$model->db_commit();
				$esitoModifica = 1;
			}elseif($esito==2){
				$msgerr = $model->getError();
			}

		}elseif($ERRCODE==1){
			$msgerr = "Attenzione! Si &eacute; verificato un errore, contattare l&rsquo;amministratore del sistema.";            
		}elseif($ERRCODE==2){
			$msgerr = $ERRTEXT;
		}
		
		$response_data = array();
 		$response_data['msgerr']            = $msgerr;
		$response_data['statoFlusso']       = $statoFlusso;
        $response_data['P_PK_SG']   		= $P_PK_SG;
        $response_data['COD_PROD']   		= $COD_PROD;
        $response_data['COD_MITT']   		= $COD_MITT;
        $response_data['p_pk_607_flusso']   = $p_pk_607_flusso;
        $response_data['esitoModifica']   	= $esitoModifica;
		$response_data['action_forward']    = 'dettaglio';

        $response->write(json_encode($response_data));

        if (is_array($response_data)) {
            $data = ["status" => "OK", "result" => $response_data];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero dell'aggiornamento del flusso"];
            return $response->withJson($data, 500);
        }
    }  
	
	public function visualizzaric(Request $request, Response $response)
    {
		$request_data = $request->getParsedBody();
        $p_pk_607_flusso       = $request_data['p_pk_607_flusso'];

		
		
		$esito   = $this->db->ritrieve_richiesta_conc($p_pk_607_flusso,$filename,$fileContent);
		
 		if($esito==1 ){
			$file_extension   = strtolower(substr(strrchr($filename,"."),1));

			switch( $file_extension ){
				case "pdf": $ctype="application/pdf"; break;
				case "exe": $ctype="application/octet-stream"; break;
				case "zip": $ctype="application/zip"; break;
				case "doc": $ctype="application/msword"; break;
				case "xls": $ctype="application/vnd.ms-excel"; break;
				case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
				case "gif": $ctype="image/gif"; break;
				case "png": $ctype="image/png"; break;
				case "jpeg":
					case "jpg": $ctype="image/jpg"; break;
				case "mp3": $ctype="audio/mpeg"; break;
				case "wav": $ctype="audio/x-wav"; break;
				case "mpeg":
					case "mpg":
					case "mpe": $ctype="video/mpeg"; break;
				case "mov": $ctype="video/quicktime"; break;
				case "avi": $ctype="video/x-msvideo"; break;

				//The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
				case "php":
					case "htm":
					case "html":
					case "txt": $ctype="text/php ; charset=iso-8859-1"; break;

				default: $ctype="application/force-download";
			}

			ob_clean();

		}
		else
		{
			$msgerr = "Attenzione! Si &eacute; verificato un errore durante la vuslizazione della richiesta, contattare l&rsquo;amministratore del sistema.";
		}

		$response_data = array();
        $response_data['error']                 = false;
		$response_data['msgerr']                = $msgerr;
		$response_data['p_pk_607_flusso']       = $p_pk_607_flusso;
        $response_data['fileContent']           = $fileContent;
		$response_data['filename']          	= $filename;
		$response_data['ctype']           		= $ctype;
		$response_data['esito']           		= $esito;
        $response->write(json_encode($response_data));

        if (is_array($response_data)) {
            $data = ["status" => "OK", "result" => $response_data];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero del documento"];
            return $response->withJson($data, 500);
        }
    }  
	
	public function responsocreazione(Request $request, Response $response)
    {
		$request_data = $request->getParsedBody();
        $nomeSistemaDiGioco       = $request_data['nomeSistemaDiGioco'];
        $nomeConcessionario       = $request_data['nomeConcessionario'];		
        $p_data_fine_invio607       = $request_data['p_data_fine_invio607'];
        $p_numero_totale_607       = $request_data['p_numero_totale_607'];		
        $p_data_ritrasm_600       = $request_data['p_data_ritrasm_600'];		
        $p_data_replica       = $request_data['p_data_replica'];		
        $p_anno600       = $request_data['p_anno600'];		
        $esitoInserimento       = $request_data['esitoInserimento'];		
		
		
		$response_data = array();
        $response_data['nomeSistemaDiGioco']         = $nomeSistemaDiGioco;
		$response_data['nomeConcessionario']         = $nomeConcessionario;
		$response_data['p_data_fine_invio607']       = $p_data_fine_invio607;
        $response_data['p_numero_totale_607']        = $p_numero_totale_607;
		$response_data['p_data_ritrasm_600']         = $p_data_ritrasm_600;
		$response_data['p_data_replica']             = $p_data_replica;
		$response_data['p_anno600']           		 = $p_anno600;
		$response_data['esitoInserimento']           = $esitoInserimento;

        $response->write(json_encode($response_data));

        if (is_array($response_data)) {
            $data = ["status" => "OK", "result" => $response_data];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero del documento"];
            return $response->withJson($data, 500);
        }
    }  	
	
	public function cruscotto(Request $request, Response $response)
    {
		$request_data = $request->getParsedBody();
        $application       = "Gestione messaggi 607 - Cruscotto";
        
		//originale ---manca this.log
		//$listaFlussi = $this->db->retrieveFlussiCruscotto($this->log);		
		$listaFlussi = $this->db->retrieveFlussiCruscotto();		
		
		$navbar->add_item("Gestione messaggi 607", "/php/videogiochi/gestione_messaggi_607/index.php", "Gestione messaggi 607");	   
		$navbar->add_item("Cruscotto", "", "Cruscotto");		
		
		$response_data = array();
        $response_data['listaFlussi']         = $listaFlussi;
		$response_data['application']         = $application;
        $response_data['navbarArray']           = $navbarArray;		

        $response->write(json_encode($response_data));

        if (is_array($response_data)) {
            $data = ["status" => "OK", "result" => $response_data];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero del documento"];
            return $response->withJson($data, 500);
        }
    }  	
	
	/*
	public function creazioneFlusso(Request $request, Response $response)
    {
        $response_data = [];
        $request_data = $request->getParsedBody();

        $application = "Gestione messaggi 607 - Creazione flusso";
        //    REPORT CONDUZIONE RETE TELEMATICA
        $a = time();
        $b = date('d M y - H:i:s', $a);
        $model = new DaoVlt($this->getLog());
        $ucr  = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);

        //qui va messo il file che si occupa dell'elaborazione del file trasferito
        $red  = "/videogiochi/gestione_messaggi_607/index.php/index/salvacreazione/";

        $response_data['ucr'] = $ucr;
        $response_data['red'] = $red;

        $COD_MITT               = $request_data['COD_MITT'];
        $PROG_SISTEMA_GIOCO_ID  = $request_data['PROG_SISTEMA_GIOCO_ID'];
        $p_data_fine_invio607   = $request_data['p_data_fine_invio607'];
        $p_numero_totale_607    = $request_data['p_numero_totale_607'];
        $p_data_ritrasm_600     = $request_data['p_data_ritrasm_600'];
        $p_data_replica         = $request_data['p_data_replica'];
        $p_richiesta_conc       = $request_data['p_richiesta_conc']; //file da uploadare
        $p_cf_operatore         = $_SESSION['cf_utente'];
        $p_anno600              = $request_data['p_anno600'];

        $response_data['msgerr'] = $request_data['esitoInserimento'];

        if ($p_numero_totale_607 == '') {
            $p_numero_totale_607 = 100;
        }

        if ($p_data_fine_invio607 == '') {
            $a = time();
            $myDate = date('Y-m-d', $a);
            $newdate = strtotime('+7 day', strtotime($myDate));
            $newdate = date('d/m/Y', $newdate);
            $p_data_fine_invio607 = $newdate;
        }

        $pathFile = $_SESSION['ChunkUpload']['PFX'];
        $cmd = "find $pathFile -print";

        $path = '/opt/web/php/tmp/server/upload/' . session_id() . '/';
        $dh  = @opendir($path);
        while (false !== ($filename = @readdir($dh))) {
            @unlink($path . $filename);
        }

        $response_data['COD_MITT']                   = $COD_MITT;
        $response_data['PROG_SISTEMA_GIOCO_ID']      = $PROG_SISTEMA_GIOCO_ID;
        $response_data['p_data_fine_invio607']       = $p_data_fine_invio607;
        $response_data['p_numero_totale_607']        = $p_numero_totale_607;
        $response_data['p_data_ritrasm_600']         = $p_data_ritrasm_600;
        $response_data['p_data_replica']             = $p_data_replica;
        $response_data['p_anno600']                  = $p_anno600;

        // lista concessionari
        $codmittList   = $model->retrieveListConc();
        if ($model->getError() != "") {
            $response_data['msgerr'] = $model->getError();
        }
        foreach ($codmittList as $key => $conc) {
            $concListView[$conc['COD_MITT']] = $conc['DENOMINAZIONE'];
            if ($conc['COD_MITT'] == $COD_MITT) {
                $nomeConcessionario = $conc['DENOMINAZIONE'];
            }
        }
        $response_data['nomeConcessionario'] = $nomeConcessionario;

        $selectConcessionari = $this->utilityClass->selectOptions($concListView, $COD_MITT);

        $flgDisabledSg                  = "disabled";
        $flgDisabledAnnoRiferimento     = "disabled";

        if ($COD_MITT != '') {
            $flgDisabledSg = "";
            $resultSistemiGioco     = $model->retrieveSg($COD_MITT);
            if ($model->getError()) {
                $response_data['msgerr']                    = $model->getError();
            }

            foreach ($resultSistemiGioco as $key => $conc) {
                $resultSistemiGiocoView[$conc['PROG_SISTEMA_GIOCO_ID']] = $conc['DENOMINAZIONE'];
                if ($conc['PROG_SISTEMA_GIOCO_ID'] == $PROG_SISTEMA_GIOCO_ID) {
                    $nomeSistemaDiGioco = $conc['DENOMINAZIONE'];
                }
            }

            $response_data['nomeSistemaDiGioco'] = $nomeSistemaDiGioco;
            $selectSistemiDiGioco   = $this->utilityClass->selectOptions($resultSistemiGiocoView, $PROG_SISTEMA_GIOCO_ID);
            if ($PROG_SISTEMA_GIOCO_ID > 0) {
                $flgDisabledAnnoRiferimento = "";
                $resultAnno             = $model->retrievePrimoAnnoValido($COD_MITT, $PROG_SISTEMA_GIOCO_ID);
                $resultAnni             = $this->utilityClass->getArrayAnni($resultAnno);
                foreach ($resultAnni as $key => $conc) {
                    $resultAnniView[$conc['ANNO_ID']] = $conc['ANNO'];
                }
            }

            $selectAnni             = $this->utilityClass->selectOptions($resultAnniView, $p_anno600);
        }

        //$this->COD_FLUSSO                   = $COD_FLUSSO;
        $response_data['selectConcessionari']            = $selectConcessionari;
        $response_data['selectSistemiDiGioco']           = $selectSistemiDiGioco;
        $response_data['selectAnniRiferimento']          = $selectAnni;

        $response_data['flgDisabledSg']                  = $flgDisabledSg;
        $response_data['flgDisabledAnnoRiferimento']     = $flgDisabledAnnoRiferimento;

        // NAVIGATION BAR
        $navbar = new NavigationBar();
        $navbar->add_item("Gestione messaggi 607", "/php/videogiochi/gestione_messaggi_607/index.php", "Gestione messaggi 607");
        $navbar->add_item("Creazione", "", "Creazione");

        $response_data['displayMenuMonitorLinks']  = 'block';
        $response_data['displayMenuMonitorLinks2'] = 'none';
        $response_data['navbarArray']              = $navbar->ShowBar();
        $response_data['application']              = $application;
        $response_data['error']                    = $model->error;


        $response->write(json_encode($response_data));

        if (is_array($response_data)) {
            $data = ["status" => "OK", "result" => $response_data];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero del monitoring"];
            return $response->withJson($data, 500);
        }
    }*/
}
