<?php

namespace NIM_Backend\Controllers;

//use \Psr\Http\Message\ServerRequestInterface as Request;
//use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
use NIM_Backend\Models\DaoFactory as DaoFactory;

class mainAppController
{
    protected $logger;
    protected $db;

    public function __construct(\Monolog\Logger $logger, DaoFactory $db)
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

    //RC01_antiric.inc --> getForm()
    public function selezionaConcessionari(Request $request, Response $response)
    {
        $concessionario = null;
        $traccolta      = null;
        $anno           = null;
        $gioco          = null;
        $tipo_old       = null;
        $traccolta_old  = null;
        $semestre_old   = null;
        $gioco_old      = null;
        $anno_old       = null;

        $request_data = $request->getParsedBody();
        if (isset($request_data['concessionario'])) {
            $concessionario = $request_data['concessionario'];
        }

        if (isset($request_data['anno'])) {
            $anno = $request_data['anno'];
        }

        if (isset($request_data['tiporaccolta'])) {
            $traccolta = $request_data['tiporaccolta'];
        }

        if (isset($request_data['tipogioco'])) {
            $gioco = $request_data['tipogioco'];
        }

        if (isset($request_data['semestre'])) {
            $semestre = $request_data['semestre'];
        } else {
            $semestre = '-1';
        }

        if (isset($request_data['anno_old'])) {
            $anno_old = $request_data['anno_old'];
        }

        if (isset($request_data['tiporaccolta_old'])) {
            $traccolta_old = $request_data['tiporaccolta_old'];
        }

        if (isset($request_data['tipogioco_old'])) {
            $gioco_old = $request_data['tipogioco_old'];
        }

        if (isset($request_data['semestre_old'])) {
            $semestre_old = $request_data['semestre_old'];
        } else {
            $semestre_old = '-1';
        }

        $responseData['concessionario'] = $concessionario;
        $responseData['tiporaccolta']   = $traccolta;
        $responseData['semestre']       = $semestre;
        $responseData['tipogioco']      = $gioco;
        $responseData['anno']           = $anno;
        $responseData['pars']           = $request_data;

        if (isset($request_data['tipoconc'])) {
            $tipo = $request_data['tipoconc'];
        } else {
            $tipo = 'T';
        }

        if (isset($request_data['tipoconc_old']) && $request_data['tipoconc_old'] != '') {
            $tipo_old = $request_data['tipoconc_old'];
        } else {
            $tipo_old = 'T';
        }

        $responseData['tipoconc_old']     = $tipo_old;
        $responseData['tiporaccolta_old'] = $traccolta_old;
        $responseData['semestre_old']     = $semestre_old;
        $responseData['tipogioco_old']    = $gioco_old;
        $responseData['anno_old']         = $anno_old;
        $responseData['tipoconc']         = $tipo;

        $request_data['visualizza'] = 'Visualizza>>';

        if (isset($request_data['visualizza']) && $request_data['visualizza'] == 'Visualizza>>') {
            $arrayBind = array(
                'tipo_conc'    => $tipo,
                'anno'         => $anno,
                'semestre'     => $semestre,
                'tipogioco'    => $gioco,
                'tiporaccolta' => $traccolta,
            );


            $res = $this->db->selezioneConcessionari($arrayBind);
            if ($res == null) {
                $responseData['messaggioConcessionari']  = 'Non sono presenti concessionari per i parametri di ';
                $responseData['messaggioConcessionari'] .= 'ricerca selezionati';
            }

            $concOpt = array();
            if ($this->db->getError() == "" && $res != null) {
                foreach ($res as $k => $v) {
                    if ($v['TIPO_CONC'] == 'VID') {
                        $concOpt[$v['TIPO_CONC'] . $v['COD_CONC']] = (isset($v['RAG_SOC']) ? $v['RAG_SOC'] : '');
                    } else {
                        $concOpt[$v['TIPO_CONC'] . $v['COD_CONC']] = $v['COD_CONC'] . ' - ' . (isset($v['RAG_SOC']) ? $v['RAG_SOC'] : '');
                    }
                }
            }

            if ($this->db->getError() == "") {
                if (isset($concOpt)) {
                    $responseData['select_concessionari'] = $concOpt;
                }
            } else {
                $responseData['messaggio'] = $this->db->getError();
            }

            $responseData['tipoconcSelected'] = $tipo;
            $responseData['concessionari']    = $concOpt;
        }

        $arrayBind   = array('allIn' => '1',);
        $res         = $this->db->selezioneTipoConcessionari($arrayBind);
        $tipoConcOpt = array();
        foreach ($res as $k => $v) {
            $tipoConcOpt[$v["TIPO_CONC"]] = $v['DESCRIZIONE'];
        }
        $responseData['tipoconcessioni'] = $tipoConcOpt;

        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero dei concessionari"];
            return $response->withJson($data, 500);
        }
    }

    function getDatiTrasmessi(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();

        $conctmp = '';
        $DWHFlag = '0';
        $tipo_conc = '';
        $concessionario = null;

        if (isset($request_data['DWH'])) {
            $DWHFlag = '1';
        }

        $responseData['pars'] = $request_data;
        if (isset($request_data['annoPuntuale'])) {
            $annoPuntuale = $request_data['annoPuntuale'];
        }

        if (isset($request_data['semestrePuntuale'])) {
            $semestrePuntuale = $request_data['semestrePuntuale'];
        }

        if (isset($request_data['tipogiocoPuntuale'])) {
            $GiocoPuntuale = $request_data['tipogiocoPuntuale'];
        }

        if (isset($request_data) && is_array($request_data) && array_key_exists('F', $request_data) && $request_data['F'] == 'Raccolta fisica') {
            $tipoRetePuntuale = 'F';
        } else if (isset($request_data) && is_array($request_data)  && array_key_exists('D', $request_data) && $request_data['D'] == 'Raccolta a distanza') {
            $tipoRetePuntuale = 'D';
        }

        if (isset($request_data['concessionario'])) {
            $concessionario = $request_data['concessionario'];
            if ($concessionario != str_replace('IPP', '', $concessionario)) {
                $conctmp   = str_replace('IPP', '', $concessionario);
                $tipo_conc = 'IPP';
            } elseif ($concessionario != str_replace('GAD', '', $concessionario)) {
                $conctmp   = str_replace('GAD', '', $concessionario);
                $tipo_conc = 'GAD';
            } elseif ($concessionario != str_replace('VID', '', $concessionario)) {
                $conctmp   = str_replace('VID', '', $concessionario);
                $tipo_conc = 'VID';
            } elseif ($concessionario != str_replace('IDLG', '', $concessionario)) {
                $conctmp   = str_replace('IDLG', '', $concessionario);
                $tipo_conc = 'IDLG';
            } elseif ($concessionario != str_replace('AI', '', $concessionario)) {
                $conctmp   = str_replace('AI', '', $concessionario);
                $tipo_conc = 'AI';
            } elseif ($concessionario != str_replace('AS', '', $concessionario)) {
                $conctmp   = str_replace('AS', '', $concessionario);
                $tipo_conc = 'AS';
            } elseif ($concessionario != (str_replace('S', '', $concessionario))) {
                $conctmp   = str_replace('S', '', $concessionario);
                $tipo_conc = 'S';
            } elseif ($concessionario != str_replace('I', '', $concessionario)) {
                $conctmp   = str_replace('I', '', $concessionario);
                $tipo_conc = 'I';
            } elseif ($concessionario != str_replace('B', '', $concessionario)) {
                $conctmp   = str_replace('B', '', $concessionario);
                $tipo_conc = 'B';
            } elseif ($concessionario != str_replace('X', '', $concessionario)) {
                $conctmp   = str_replace('X', '', $concessionario);
                $tipo_conc = 'X';
            } elseif ($concessionario != str_replace('A', '', $concessionario)) {
                $conctmp   = str_replace('A', '', $concessionario);
                $tipo_conc = 'A';
            }
        }

        if (isset($request_data['anno'])) {
            $anno = $request_data['anno'];
        } else {
            $anno = -1;
        }

        if (isset($request_data['tiporaccolta'])) {
            $traccolta = $request_data['tiporaccolta'];
        } else {
            $traccolta = 'T';
        }

        if (isset($request_data['tipogioco'])) {
            $gioco = $request_data['tipogioco'];
        } else {
            $gioco = '-1';
        }

        if (isset($request_data['semestre'])) {
            $semestre = $request_data['semestre'];
        } else {
            $semestre = '-1';
        }

        if ($anno != -1) {
            $caption = 'Anno ' . $anno;
        } else {
            $caption = 'Anno tutti';
        }

        if ($semestre != -1) {
            $caption .= '<br /> Periodo ';
            $caption .= $semestre == 2 ? 'secondo semestre' : 'primo semestre';
        } else {
            $caption .= '<br /> Periodo tutti';
        }

        if ($tipo_conc != 'GAD') {
            if ($traccolta != 'T') {
                $caption .= ' <br /> Tipo Raccolta ';
                $caption .= $traccolta == 'F' ? 'fisica' : 'a distanza';
            } else {
                $caption .= ' <br /> Tipo raccolta fisica e a distanza';
            }
        }

        $responseData['concessionario'] = $concessionario;
        $responseData['concessione']    = $conctmp;
        $responseData['tiporaccolta']   = $traccolta;
        $responseData['semestre']       = $semestre;
        $responseData['tipogioco']      = "$gioco";
        $responseData['anno']           = $anno;
        $responseData['tipoconc']       = $tipo_conc;
        $cf                              = $request_data['cf_utente'];


        $arrayBind                       = array(
            'tipo_concIn' => $tipo_conc,
            'cod_concIn'  => $conctmp,
            'annoIn'      => $anno,
            'semestreIn'  => $semestre,
            'cod_giocoIn' => "$gioco",
            'tipo_reteIn' => $traccolta,
            'DWHFlag'     => $DWHFlag,
            'cfaamsIn'    => $cf,
            'subarea'     => 1,
        );

        if (
            $tipo_conc == 'VID' || $tipo_conc == 'B' || $tipo_conc == 'AI' ||
            $tipo_conc == 'AS'
        ) {
            $responseData['larghezzatabella'] = '40%';
        } else {
            $responseData['larghezzatabella'] = '100%';
        }

        $res = $this->db->selezioneDatiTrasmessi($arrayBind);
        if ($this->db->getError() == "") {
            if (isset($res)) {
                /*
                    Se giochi = -1 allora faccio vedere tutti i giochi,
                    altrimenti faccio vedere solo il gioco selezionato e
                    adatto la tabella alla visualizzazione di un solo gioco.
                */

                if ($gioco == -1) {
                    $pars = array(
                        'tipo_conc' => $tipo_conc,
                        'tipo_rete' => $traccolta,
                        'cod_gioco' => null,
                        'anno'      => null,
                        'semestre'  => null,
                    );
                    if (!$giochi = $this->_selGiochi($pars)) {
                        $giochi = $this->calcolaGiochi2($tipo_conc, $traccolta);
                    }

                    $caption .= '<br /> Tipo gioco Tutti';
                } else {
                    $pars = array(
                        'tipo_conc' => $tipo_conc,
                        'tipo_rete' => $traccolta,
                        'cod_gioco' => $gioco,
                        'anno'      => null,
                        'semestre'  => null,
                    );
                    if (!$giochi = $this->_selGiochi($pars)) {
                        $giochi = $this->calcolaGiochi3($tipo_conc, $gioco);
                    }

                    $responseData['larghezzatabella'] = '40%';
                    $caption                          .=
                        '<br /> Tipo gioco ' . $giochi['DESCR_GIOCO'][0];
                }

                /*
                    Se anno = -1 (TUTTI) allora richiamo la funzione
                    calcolaAnni2 che calcola gli anni a partire dal 2011 Primo
                    semestre per la costruzione della tabella, altrimenti
                    calcolo gli anni, semestri in base all'input dell'anno !=-1
                    e al semestre che può valere ={1,2,-1}.
                */

                if ($anno == -1) {
                    $anni = $this->calcolaAnni2($res);
                } else {
                    $anni = $this->calcolaAnni3($res, $anno, $semestre);
                }

                $responseData['result'] = $res;
                $responseData['giochi'] = $giochi;
                $responseData['anni']   = $anni;
            }
        } else {
            $responseData['messaggio'] = $this->db->getError();
        }

        if (isset($tipoRetePuntuale)) {
            $arrayBind            = array(
                'tipo_concIn' => $tipo_conc,
                'cod_concIn'  => $conctmp,
                'annoIn'      => $annoPuntuale,
                'semestreIn'  => $semestrePuntuale,
                'cod_giocoIn' => "$GiocoPuntuale",
                'tipo_reteIn' => $tipoRetePuntuale,
                'DWHFlag'     => $DWHFlag,
                'cfaamsIn'    => $cf,
                'subarea'     => 1,
            );
            $this->db->arr_output = null;
            $tres3 = $this->db->selezioneDatiTrasmessi($arrayBind);
            if ($this->db->getError() != "") {
                $responseData['messaggio'] = $this->db->getError();
            } else if ($tres3 == null) {
                $responseData['messaggio'] = $this->db->getError();
            } else {
                $responseData['resultPuntuale'] = $tres3;
            }
        }

        $responseData['caption'] = $caption;

        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero dei dati trasmessi"];
            return $response->withJson($data, 500);
        }
    }


    function getDatiTrasmessiCSV(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();

        $responseData['pars'] = $request_data;
        if (isset($request_data['concessionario'])) {
            $concessionario = $request_data['concessionario'];
            if ($concessionario != str_replace('IPP', '', $concessionario)) {
                $conctmp   = str_replace('IPP', '', $concessionario);
                $tipo_conc = 'IPP';
            } elseif ($concessionario != str_replace(
                'GAD',
                '',
                $concessionario
            )) {
                $conctmp   = str_replace('GAD', '', $concessionario);
                $tipo_conc = 'GAD';
            } elseif ($concessionario != str_replace(
                'VID',
                '',
                $concessionario
            )) {
                $conctmp   = str_replace('VID', '', $concessionario);
                $tipo_conc = 'VID';
            } elseif ($concessionario != str_replace(
                'IDLG',
                '',
                $concessionario
            )) {
                $conctmp   = str_replace('IDLG', '', $concessionario);
                $tipo_conc = 'IDLG';
            } elseif ($concessionario != str_replace(
                'AI',
                '',
                $concessionario
            )) {
                $conctmp   = str_replace('AI', '', $concessionario);
                $tipo_conc = 'AI';
            } elseif ($concessionario != str_replace(
                'AS',
                '',
                $concessionario
            )) {
                $conctmp   = str_replace('AS', '', $concessionario);
                $tipo_conc = 'AS';
            } elseif ($concessionario != (str_replace(
                'S',
                '',
                $concessionario
            ))) {
                $conctmp   = str_replace('S', '', $concessionario);
                $tipo_conc = 'S';
            } elseif ($concessionario != str_replace(
                'I',
                '',
                $concessionario
            )) {
                $conctmp   = str_replace('I', '', $concessionario);
                $tipo_conc = 'I';
            } elseif ($concessionario != str_replace(
                'B',
                '',
                $concessionario
            )) {
                $conctmp   = str_replace('B', '', $concessionario);
                $tipo_conc = 'B';
            } elseif ($concessionario != str_replace(
                'X',
                '',
                $concessionario
            )) {
                $conctmp   = str_replace('X', '', $concessionario);
                $tipo_conc = 'X';
            } elseif ($concessionario != str_replace(
                'A',
                '',
                $concessionario
            )) {
                $conctmp   = str_replace('A', '', $concessionario);
                $tipo_conc = 'A';
            }
        }

        if (isset($request_data['anno'])) {
            $anno = $request_data['anno'];
        } else {
            $anno = -1;
        }

        if (isset($request_data['tiporaccolta'])) {
            $traccolta = $request_data['tiporaccolta'];
        } else {
            $traccolta = 'T';
        }

        if (isset($request_data['tipogioco'])) {
            $gioco = $request_data['tipogioco'];
        } else {
            $gioco = '-1';
        }

        if (isset($request_data['semestre'])) {
            $semestre = $request_data['semestre'];
        } else {
            $semestre = '-1';
        }

        $responseData['concessionario'] = $concessionario;
        $responseData['tiporaccolta']   = $traccolta;
        $responseData['semestre']       = $semestre;
        $responseData['tipogioco']      = $gioco;
        $responseData['anno']           = $anno;
        $responseData['tipoconc']       = $tipo_conc;
        $arrayBind                       = array(
            'tipo_concIn' => $tipo_conc,
            'cod_concIn'  => $conctmp,
            'annoIn'      => $anno,
            'semestreIn'  => $semestre,
            'cod_giocoIn' => $gioco,
            'tipo_reteIn' => $traccolta,

        );
        $res = $this->db->selezioneDatiTrasmessiCSV($arrayBind);
        if ($tipo_conc == 'GAD') {
            if ($anno == -1 && $gioco == -1) {
                $message = 'NE';
                $keyMax  = 0;
                foreach ($res['Q5'] as $key => $value) {
                    if ($res['Q5'][$key] == '-1.000') {
                        $res['Q5'][$key] = $message;
                    }

                    if ($res['Q6'][$key] == '-1.000') {
                        $res['Q6'][$key] = $message;
                    }

                    if ($res['Q7'][$key] == '-1.000') {
                        $res['Q7'][$key] = $message;
                    }

                    if ($res['Q8'][$key] == '-1.000') {
                        $res['Q8'][$key] = $message;
                    }

                    $keyMax = $keyMax;
                }
            } else {
                unset($res['Q5']);
                unset($res['Q6']);
                unset($res['Q7']);
                unset($res['Q8']);
            }
        }
        $responseData['elenco']   = $res;
        $responseData['sezione']  = 'DATI ANTIRICICLAGGIO';
        $responseData['sezione'] .= "\n\n Legenda:";
        $responseData['sezione'] .=
            "\nND = Non disponibile NE = Non esiste DWH = data warehouse\n";
        if ($tipo_conc == 'GAD' && $anno == -1 && $gioco == -1) {
            $responseData['sezione'] .=
                "\n Anno 2011 \n Q1=Giocate superiori a 1.000 euro ";
            $responseData['sezione'] .=
                'Q2=Vincite superiori a 1.000 euro Q3=Operazioni frazionate ';
            $responseData['sezione'] .=
                'Q4=Operazioni sospette Q5=Non presente Q6=Non presente ';
            $responseData['sezione'] .= 'Q7=Non presente Q8=Non presente\n';
            $responseData['sezione'] .=
                "\n Anni Maggiori del 2011 \nQ1=Ricariche 1.000 ";
            $responseData['sezione'] .=
                'Q2=Ricariche 15.000 Q3=Prelievi 1.000 Q4=Prelievi 15.000 ';
            $responseData['sezione'] .=
                'Q5 = Carte 1.000 Q6=Carte 15.000 Q7=Carte Torneo 15.000 ';
            $responseData['sezione'] .= "Q8=Operazioni sospette \n\n";
        }

        $responseData['titolo_tabella'] = 'Rapporto Concessorio - Monitoraggio<br>Storia della concessione';
        $responseData['tpl_name'] = $conctmp;

        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero dei dati trasmessi"];
            return $response->withJson($data, 500);
        }
    }

    /**
     * Restituisce codice gioco e descrizione su ricerca generica o puntuale.
     *
     * Se riceve il codice gioco fornisce la descrizione del gioco.
     *
     * @param array $pars Array di tipo conc, rete, anno, semestre e gioco.
     *
     * @return array $giochi Resulset con cod_gioco e descr_gioco.
     */
    function selezioneGiochi(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();
        $responseData['giochi'] = null;
        $pars = array(
            'tipo_conc' => $request_data['tipo_conc'],
            'tipo_rete' => $request_data['traccolta'],
            'cod_gioco' => $request_data['cod_gioco'],
            'anno'      => $request_data['anno'],
            'semestre'  => $request_data['semestre'],
        );

        if (count($pars)) {
            $retVal = $this->db->selezioneGiochi($pars);
            if ($this->db->getError() != "") {
                $responseData['messaggio'] = $this->db->getError();
            } else if ($retVal == null) {
                $responseData['messaggio'] = $this->db->getError();
            } else {
                $responseData['giochi'] = $retVal;
            }
        }

        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero dei giochi"];
            return $response->withJson($data, 500);
        }
    }

    function _selGiochi($pars)
    {
        if (count($pars)) {
            return $this->db->selezioneGiochi($pars);
        } else {
            return null;
        }
    }

    /**
     * Costruisce option per menu a tendina scelta giochi.
     *
     * @param string $tipoConc Tipo Concessione.
     *
     * @return array $giochi Array con chiave=cod_gioco e valore=descr_gioco.
     */
    function giochiDisponibiliPerConcessione(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();
        $tipoConc = $request_data['tipoConc'];
        if ($tipoConc == 'T') {
            $tipoConc = null;
        }

        $pars   = array(
            'tipo_conc' => $tipoConc,
            'tipo_rete' => null,
            'cod_gioco' => null,
            'anno'      => null,
            'semestre'  => null,
        );

        $giochi = $this->db->selezioneGiochi($pars);
        if ($giochi && is_array($giochi) && count($giochi)) {
            $responseData['tipogiochi'] = array();
            foreach ($giochi as $k => $v) {
                $responseData['tipogiochi'][$v['COD_GIOCO']] = $v['DESCR_GIOCO'];
            }
        }

        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero dei giochi disponibili"];
            return $response->withJson($data, 500);
        }
    }
	
    //RC01_antiric_monitoraggio.inc 
    public function getForm(Request $request, Response $response)
    {
        $anno_r         = null;
        $semestre_r     = null;
        $tipogioco      = null;
        $tiporete       = null;
        $anno           = null;
        $semestre  = null;
        $tipoconc   = null;

        $gioco_old      = null;
        $anno_old       = null;

        $request_data = $request->getParsedBody();
        if (isset($request_data['anno_r'])) {
            $anno_r = $request_data['anno_r'];
        }

        if (isset($request_data['semestre_r'])) {
            $semestre_r = $request_data['semestre_r'];
        }

        if (isset($request_data['tipogioco'])) {
            $tipogioco = $request_data['tipogioco'];
        }

        if (isset($request_data['tiporete'])) {
            $tiporete = $request_data['tiporete'];
        }

        if (isset($request_data['anno'])) {
            $anno = $request_data['anno'];
        } else {
            $anno = '-1';
        }

        if (isset($request_data['semestre'])) {
            $semestre = $request_data['semestre'];
        }

        if (isset($request_data['tipoconc'])) {
            $tipoconc = $request_data['tipoconc'];
        }

        $responseData['tipo_conc']        = $tipo_conc;
        $responseData['tiporete']         = $tiporete;
        $responseData['tipogioco']        = $tipogioco;
        $responseData['anno']             = $anno;
        $responseData['semestre']         = $semestre;
        $responseData['tipoconcSelected'] = $tipo_conc;

        if (isset($request_data['tipo_conc_visualizza'])) {
            $tipo_conc_visualizza = $request_data['tipo_conc_visualizza'];
        }

        if (isset($request_data['visualizza']) && $request_data['visualizza'] == 'Visualizza>>') {
            $responseData['tipoconc_visualizza'] = $tipo_conc;
            $tipo_conc_visualizza                 = $tipo_conc;
        } else {
            $this->tpl_dat['tipoconc_visualizza'] = isset($this->clear_pars['tipoconc_visualizza']) ? $this->clear_pars['tipoconc_visualizza'] : null;
        }


        $elencogiochi = $this->giochiDisponibiliPerConcessioneNew($tipo_conc);
        if(!is_array($elencogiochi)) {
            $giochi = $this->giochiDisponibiliPerConcessioneMonitoraggioInc($tipo_conc_visualizza);
        }
        $responseData['elencogiochi'] = $elencogiochi;

        $arrayBind   = array('allIn' => '1',);
        $res         = $this->db->selezioneTipoConcessionari($arrayBind);
        $tipoConcOpt = array();
        foreach ($res as $k => $v) {
            $tipoConcOpt[$v["TIPO_CONC"]] = $v['DESCRIZIONE'];
        }
        $responseData['tipoconcessioni'] = $tipoConcOpt;

        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero dei concessionari"];
            return $response->withJson($data, 500);
        }
        
    }

    public function getResult(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();
        $anno             = $request_data['anno'];
        $semestre         = $request_data['semestre'];
        $tipoconc         = $request_data['tipoconc'];
        $tipogioco        = $request_data['tipogioco']; 
        $tiporete         = $request_data['tiporete'] ;


        $responseData['anno']      = $anno;
        $responseData['semestre']  = $semestre;
        $responseData['tipoconc']  = $tipoconc;
        $responseData['tipogioco'] = $tipogioco;

        $cf         = $request_data['cf_utente'] ;
        $responseData['tiporete'] = $tiporete;

        if ($this->_checkGioco($tipo_conc, $tipogioco, $anno) == 0 ||
        $this->_controllaPeriodoConcessione($tipo_conc, $semestre,
                $anno, $tiporete, $tipogioco) == 0
        || !$this->_vincoli($tipo_conc, $anno, $semestre, $tiporete)
        ) {
            $responseData['messaggio'] = 'Dati di ricerca incongruenti';
            return -1;
        } elseif ($this->_controllaPeriodoConcessione($tipo_conc, $semestre, $anno, $tiporete, $tipogioco) == -1) {
            $responseData['messaggio'] = 'Non si possono effettuare ricerche per periodi non conclusi';
            return -1;
        }

    }   

    function _controllaPeriodoConcessione($tipoconc, $periodo, $anno, $tiporete, $codgioco)
    {
         $annoAttuale = date('y');
         $meseAttuale = date('m');
         // $meseAttuale = '07';
 
         if ($codgioco == 7 && $tiporete == 'F') {
             return 0;
         }
 
         // Caso I semestre non concluso oppure concluso.
         if ($annoAttuale == substr($anno, 2) && $periodo == 1 &&
         $meseAttuale <= '06') {
             $step = 1;
             return -1;
         } else if ($annoAttuale == substr($anno, 2) && $periodo == 2) {
             $step = 22;
             return -1;
         } elseif ($tiporete == 'F' && $tipoconc == 'GAD') {
             $step = 3;
             return 0;
         } elseif (($tipoconc == 'VID' && $tiporete == 'D') ||
         ($tipoconc == 'IDLG' && $tiporete == 'D')) {
             return 0;
         } else {
             $step = 8;
             return 1;
         }
    }
    
    function _checkGioco($tipoConc, $gioco, $anno=null) 
    {

       if ($gioco == -1) {
           return 1;
       }

       switch ($tipoConc) {
           case 'S':
               if ($gioco != 6 && $gioco != 2 && $gioco != 5 && $gioco != 7) {
                   return 0;
               }
           break;

           case'I':
               if ($gioco != 1 && $gioco != 2 && $gioco != 5 && $gioco != 7) {
                   return 0;
               }
           break;

           case'IDLG':
               if ($gioco != 1 && $gioco != 5) {
                   return 0;
               }
           break;

           case 'IPP':
           case 'AI':
               if ($gioco != 1) {
                   return 0;
               }
           break;

           case 'AS':
               if ($gioco != 6) {
                   return 0;
               }
           break;

           case 'B':
               if ($gioco != 8) {
                   return 0;
               }
           break;

           case 'GAD':
               if ($anno == 2011) {
                   if ($gioco != 1 && $gioco != 5 && $gioco != 2 &&
                   $gioco != 6 && $gioco != 7 && $gioco != 8) {
                       return 0;
                   }
               } else {
                   if ($gioco != 0) {
                       return 0;
                   }
               }
           break;

           case 'VID':
               if ($gioco != '10') {
                   return 0;
               }
           break;

           case'X':
               if ($gioco != 1 && $gioco != 2 && $gioco != 5 && $gioco != 6) {
                   return 0;
               }
           break;
       }
       
       return 1;
    }

    function _vincoli($tipo_conc, $anno, $semestre, $tipo_rete) 
    {
        $result = null;
        $step   = 0;
        if ($tipo_rete == 'F' && $tipo_conc == 'GAD') {
            $step   = 1;
            $result = false;
        } elseif ($anno == 2011 && $semestre == 1) {
            if ($tipo_conc == 'GAD') {
                $step   = 2;
                // $result = false;
                $result = true;
            } else {
                $step   = 3;
                $result = true;
            }
        } elseif (($anno == 2011 && $semestre == 2) || $anno > 2011) {
            if ($tipo_conc == 'GAD' && $tipo_rete == 'F') {
                $step   = 4;
                $result = false;
            } elseif ($tipo_conc != 'GAD' && $tipo_rete == 'D') {
                $step   = 5;
                $result = false;
            } elseif ($tipo_conc == 'GAD' && $tipo_rete == 'D') {
                $step   = 6;
                $result = true;
            } elseif ($tipo_conc != 'GAD' && $tipo_rete == 'F') {
                $step   = 7;
                $result = true;
            }
        } elseif ($anno == -1 && $tipo_conc == 'GAD') {
            $step   = 8;
            $result = true;
        }
        // ECHO "\nstep=".$step;
        // ECHO "\nresult=".$result;
        return $result;
    }

    function checkInput(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();
        $anno             = $request_data['anno'];
        $semestre         = $request_data['semestre'];
        $tipoconc         = $request_data['tipoconc'];
        $tipogioco        = $request_data['tipogioco']; 
        $tiporete         = $request_data['tiporete'] ;

        if ($semestre != null && $semestre != '' &&
                $anno != null &&  $anno != '' &&
                $tipogioco != null &&$tipogioco != '' &&
                $tipoconc != null && $tipoconc != '' &&
                $tiporete != null && $tiporete != ''
        ) 
        {
            $retval =  true;
            $responseData['messaggio']="";
        } 
        else 
        {
            $retval =  false;
            $responseData['messaggio'] = 'I Campi sono obbligatori';
            return false;
        }

        $responseData['tipoconcessioni'] = $tipoConcOpt;
        $responseData['retval'] = $retval;

        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il controllo dei parametri"];
            return $response->withJson($data, 500);
        }
    }

    function calcolaAnno(Request $request, Response $response)
    {
        $anno     = date('y');
        $arrAnno  = Array();
        $partenza = 11;
        $count    = 0;
        while ($anno - $partenza >= 0) {
            if ($anno > 9) {
                $arrAnno['20'.$anno] = '20'.$anno;
            } else {
                $arrAnno['200'.$anno] = '200'.$anno;
            }
            $count++;
            $anno--;
        }

        $responseData['arrAnno'] = $arrAnno;

        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il controllo dei parametri"];
            return $response->withJson($data, 500);
        }

    }

    function getCSV(Request $request, Response $response) 
    {

        $request_data = $request->getParsedBody();
        $anno             = $request_data['anno'];
        $semestre         = $request_data['semestre'];
        $tipoconc         = $request_data['tipoconc'];
        $tipogioco        = $request_data['tipogioco']; 
        $tiporete         = $request_data['tiporete'] ;
        $paginaCSV         = $request_data['paginaCSV'] ;

        $responseData['anno']      = $anno;
        $responseData['semestre']  = $semestre;
        $responseData['tipoconc']  = $tipoconc;
        $responseData['tipogioco'] = $tipogioco;
        $responseData['tiporete'] = $tiporete;


        if (isset($paginaCSV) && $paginaCSV) {
            $pagina = $paginaCSV;
        } else {
            $pagina                        = 1;
            $responseData['paginaCSV'] = $pagina;
        }

        $arrayBind = array(
            'tipo_concIn' => $tipo_conc,
            'annoIn' => $anno,
            'semestreIn' => $semestre,
            'cod_giocoIn' => $tipogioco,
            'tipo_reteIn' => $tiporete,
            'cfaamsIn' => '',
            'pagina' => null,
            'num_res' => null
        );

        $paging    = 40;
        $res  = $this->db->selElencoInadempienti($arrayBind);
        if ($this->db->getError() != "") {
            $responseData['messaggio'] = $this->db->getError();
        } else if ($retVal == null) {
            $responseData['messaggio'] = $this->db->getError();
        } else {
            if ($tipogioco != -1) {
                $descr_gioco                       = $res[0]['DESCR_GIOCO'];
                $responseData['tipo_concessione']  = $res[0]['DESCR'];
                $campi                             = array(
                                                      'COD_CONC' => 'Codice concessione',
                                                      'RAG_SOC' => 'Ragione Sociale',
                                                      'DATA_CONC' => 'Data stipula concessione'
                                                     );
            } else {
                $descr_gioco                       = 'TUTTI';
                $responseData['tipo_concessione']  = $res[0]['DESCR'];
                $campi                             = array(
                                                      'COD_CONC'    => 'Codice concessione',
                                                      'RAG_SOC'     => 'Ragione Sociale',
                                                      'DATA_CONC'   => 'Data stipula concessione',
                                                      'DESCR_GIOCO' => 'Tipo gioco non trasmesso'
                                                     );
            }
            if($tipo_conc == 'VID') {
                if(is_array($res) && count($res)) {
                    foreach($res as $k => $v) {
                        $res[$k]['COD_CONC'] = 'ND';
                    }
                }
            }

            $res = $this->_trasposizioneMatrice($res, $campi);
            $responseData['elenco'] = $res;
            if ($tipo_conc != 'GAD') {
                if ($tiporete == 'F') {
                    $tiporeteSTR = '\n Tipo raccolta fisica \n';
                } else {
                    $tiporeteSTR = '\n Tipo raccolta a distanza \n';
                }
            } else {
                $tiporeteSTR = '\n';
            }

            if ($semestre == 1) {
                $semestreSTR = ' primo semestre';
            } else {
                $semestreSTR = ' secondo semestre';
            }
            
            $annosemestre = " \n anno ".$anno.$semestreSTR;
            $responseData['sezione'] = 'Antiriciclaggio - Elenco concessionari inadempienti '.
            $responseData['tipo_concessione']."\n\n  "
                    . $annosemestre . $tiporeteSTR . 'Tipo gioco ' . $descr_gioco;
            $responseData['tpl_name']= 'Monitoraggio'.$responseData['tipo_concessione'];
        }


        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il reperimento csv"];
            return $response->withJson($data, 500);
        }
    }
 
    function _trasposizioneMatrice($matrice, $campi) 
    {
        foreach ($matrice as $key => $value) {
            foreach ($campi as $key2 => $campo) {
                if (isset($matrice[$key][$key2])) {
                    $var = $matrice[$key][$key2];
                    $var = str_replace('<br /><br />', ',', $var);
                    $var = str_replace('-', '', $var);

                    // $var=str_replace($var, '#', ' ');

                    $matriceI[$campo][$key] = $var;
                } else {
                    $matriceI[$campo][$key] = " ";
                }
            }
        }

        return $matriceI;
    }

    function giochiDisponibiliPerConcessioneNew($tipoConc=null)
    {
          $pars = array(
                   'tipo_conc' => $tipoConc,
                   'tipo_rete' => null,
                   'cod_gioco' => null,
                   'anno'      => null,
                   'semestre'  => null,
                  );
        $giochi = $this->_selGiochi($pars);
        if ($giochi && is_array($giochi) && count($giochi)) {
            $elencogiochi = array();
            foreach ($giochi as $k => $v) {
                $elencogiochi[$v['COD_GIOCO']] =
                $v['DESCR_GIOCO'];
            }
        }

        return $elencogiochi;
    }

    function giochiDisponibiliPerConcessioneMonitoraggioInc($tipoConc)
    {
        switch ($tipoConc) {
            case 'S':
                $elencogiochi =
                array(
                 '6' => 'SCOMMESSE A QUOTA FISSA NON IPPICHE',
                 '2' => 'SCOMMESSE A TOTALIZZATORE NON IPPICHE',
                 '5' => 'IPPICA NAZIONALE',
                 '7' => "GIOCHI DI ABILITA'",
                );
            break;

            case'IDLG':
                $elencogiochi =
                array(
                 '1' => 'SCOMMESSE IPPICHE A TOTALIZZATORE E A QUOTA FISSA',
                 '5' => 'IPPICA NAZIONALE',
                );
            break;

            case 'I':
                $elencogiochi =
                array(
                 '1' => 'SCOMMESSE IPPICHE A TOTALIZZATORE E A QUOTA FISSA',
                 '2' => 'SCOMMESSE A TOTALIZZATORE NON IPPICHE',
                 '5' => 'IPPICA NAZIONALE',
                 '7' => "GIOCHI DI ABILITA'",
                );
            break;

            case 'AI':
                $elencogiochi =
                array(
                 '1' => 'SCOMMESSE IPPICHE A TOTALIZZATORE E A QUOTA FISSA',
                );
            break;

            case 'IPP':
                $elencogiochi =
                array(
                 '1' => 'SCOMMESSE IPPICHE A TOTALIZZATORE E A QUOTA FISSA',
                );
            break;

            case 'AS':
                $elencogiochi =
                array(
                 '6' => 'SCOMMESSE A QUOTA FISSA NON IPPICHE',
                );
            break;

            case 'B':
                $elencogiochi =
                array(
                 '8' => 'BINGO',
                );
            break;

            case 'GAD':
                $elencogiochi =
                array(
                 '1' => 'SCOMMESSE IPPICHE A TOTALIZZATORE E A QUOTA FISSA',
                 '5' => 'IPPICA NAZIONALE',
                 '2' => 'SCOMMESSE A TOTALIZZATORE NON IPPICHE',
                 '6' => 'SCOMMESSE A QUOTA FISSA NON IPPICHE',
                 '7' => "GIOCHI DI ABILITA'",
                 '8' => 'BINGO',
                 '0' => 'GIOCO A DISTANZA',
                );
            break;

            case 'VID':
                $elencogiochi = array(
                                                  '10' => 'VIDEOGIOCHI',
                                                 );
            break;

            case 'X':
                $elencogiochi =
                array(
                 '1' => 'SCOMMESSE IPPICHE A TOTALIZZATORE E A QUOTA FISSA',
                 '2' => 'SCOMMESSE A TOTALIZZATORE NON IPPICHE',
                 '5' => 'IPPICA NAZIONALE',
                 '6' => 'SCOMMESSE A QUOTA FISSA NON IPPICHE',
                );
            break;

            default:
            $elencogiochi =
                array(
                 '0'  => 'GIOCO A DISTANZA',
                 '1'  => 'SCOMMESSE IPPICHE A TOTALIZZATORE E A QUOTA FISSA',
                 '5'  => 'IPPICA NAZIONALE',
                 '2'  => 'SCOMMESSE A TOTALIZZATORE NON IPPICHE',
                 '6'  => 'SCOMMESSE A QUOTA FISSA NON IPPICHE',
                 '7'  => "GIOCHI DI ABILITA'",
                 '8'  => 'BINGO',
                 '10' => 'VIDEOGIOCHI',
                );
            break;
        }

        return $elencogiochi;
    }


    function getResult1(Request $request, Response $response) 
    {
        $request_data = $request->getParsedBody();
        $anno_r             = $request_data['anno_r'];
        $semestre_r         = $request_data['semestre_r'];
        $cf_utente         = $request_data['cf_utente'];

        $arrayBind                   = array(
                                        'in_anno'     => $anno_r,
                                        'in_semestre' => $semestre_r,
                                        'cfaamsIn'    => $cf,
                                       );

        $res = $this->db->selProspetti($arrayBind);
        if ($this->db->getError() != "") {
            $responseData['messaggio'] = $this->db->getError();
        } else if ($retVal == null) {
            $responseData['messaggio'] = 'Prospetto riepilogativo selezionato non disponibile';
        } else {
            $responseData['dati_post']   = $this->clear_pars;
            $responseData['show_ancora'] = 1;
            $responseData['elenco']      = $res;
        }

        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il reperimento del prospetto riepilogativo"];
            return $response->withJson($data, 500);
        }
    }


    function getCSV1(Request $request, Response $response) 
    {
        $request_data = $request->getParsedBody();
        $anno_r             = $request_data['anno_r'];
        $semestre_r         = $request_data['semestre_r'];
        $cf_utente         = $request_data['cf_utente'];

        $arrayBind                   = array(
                                        'in_anno'     => $anno_r,
                                        'in_semestre' => $semestre_r,
                                        'cfaamsIn'    => $cf,
                                       );

        $res = $this->db->selProspetti($arrayBind);
        if ($this->db->getError() != "") {
            $responseData['messaggio'] = $this->db->getError();
        } else if ($retVal == null) {
            $responseData['messaggio'] = 'Prospetto riepilogativo selezionato non disponibile';
        } else {
            $campi = array(
                'ANNO'                => 'Anno',
                'SEMESTRE'            => 'Semestre',
                'TOT_GP_OK_NON'       => 'Giochi Ippici e Sportivi non attivi che hanno trasmesso',
                'TOT_BINGO_OK_NON'    => 'Bingo non attivi che hanno trasmesso',
                'TOT_GAD_OK_NON'      => 'GAD non attivi che hanno trasmesso',
                'TOT_VID_OK_NON'      => 'Apparecchi da intrattenimento non attivi che hanno trasmesso',
                'TOT_OK_NON'          => 'non attivi che hanno trasmesso',
                'TOT_GP_OK_DWH_OK'    => 'Giochi Ippici e Sportivi che hanno trasmesso dati coincidenti con DataWareHouse',
                'TOT_BINGO_OK_DWH_OK' => 'Bingo che hanno trasmesso dati coincidenti con DataWareHouse',
                'TOT_GAD_OK_DWH_OK'   => 'GAD che hanno trasmesso dati coincidenti con DataWareHouse',
                'TOT_VID_OK_DWH_OK'   => 'Apparecchi da intrattenimento che hanno trasmesso dati coincidenti con DataWareHouse',
                'TOT_OK_DWH_OK'       => 'attivi che hanno trasmesso dati coincidenti con DataWareHouse',
                'TOT_GP_OK_DWH_KO'    => 'Giochi Ippici e Sportivi che hanno trasmesso dati diversi da DataWareHouse',
                'TOT_BINGO_OK_DWH_KO' => 'Bingo che hanno trasmesso dati diversi da DataWareHouse',
                'TOT_GAD_OK_DWH_KO'   => 'GAD che hanno trasmesso dati diversi da DataWareHouse',
                'TOT_VID_OK_DWH_KO'   => 'Apparecchi da intrattenimento che hanno trasmesso dati diversi da DataWareHouse',
                'TOT_OK_DWH_KO'       => 'che hanno trasmesso dati diversi da DataWareHouse',
                'TOT_GP_OK'           => 'Giochi Ippici e Sportivi che hanno trasmesso',
                'TOT_BINGO_OK'        => 'Bingo che hanno trasmesso',
                'TOT_GAD_OK'          => 'GAD che hanno trasmesso',
                'TOT_VID_OK'          => 'Apparecchi da intrattenimento che hanno trasmesso',
                'TOT_OK'              => 'che hanno trasmesso',
                'TOT_GP_KO'           => 'Giochi Ippici e Sportivi inadempienti',
                'TOT_BINGO_KO'        => 'Bingo inadempienti',
                'TOT_GAD_KO'          => 'GAD inadempienti',
                'TOT_VID_KO'          => 'Apparecchi da intrattenimento inadempienti',
                'TOT_KO'              => 'inadempienti',
                'TOT_GP_CONC'         => 'Giochi Ippici e Sportivi attivi',
                'TOT_BINGO_CONC'      => 'Bingo attivi',
                'TOT_GAD_CONC'        => 'GAD attivi',
                'TOT_VID_CONC'        => 'Apparecchi da intrattenimento attivi',
                'TOT_CONC'            => 'attivi'
               );

                $res = $this->_trasposizioneMatrice($res, $campi);
                $responseData['show_ancora'] = 1;
                $responseData['elenco']      = $res;
                $responseData['sezione']     = 'Rapporto Concessorio Antiriciclaggio - Monitoraggio \n\n ANNO '
                                                .$anno? $anno : 'tutti'.' SEMESTRE '. $semestre? $semestre : 'tutti';
                $responseData['tpl_name']    = 'Rapporto Concessorio Antiriciclaggio - Monitoraggio';
        }
    }

    function getResult1det(Request $request, Response $response) 
    {
        $request_data = $request->getParsedBody();
        $dettLinkAnno             = $request_data['dettLinkAnno'];
        $dettLinkSem         = $request_data['dettLinkSem'];
        $dettlink         = $request_data['dettlink'];
        $cf_utente         = $request_data['cf_utente'];
        $paging                = 40;

        if (isset($request_data['pagina']) && $request_data['pagina']) {
            $pagina = $request_data['pagina'];
        } else {
            $pagina                     = 1;
            $responseData['pagina'] = $pagina;
        }
        /*
         * Trucchetto per mantenere il numero di pagina quando si richiede il CSV,
         * Faccio questo perché altrimenti perderei il parametro pagina
         */
        if ($pagina != null && $pagina != '') {
            $responseData['paginaCSV'] = $pagina;
        }
        /*
          End Trucchetto
        */

        if( isset($request_data['forn_ord_campo']) && $request_data['forn_ord_campo']){
            if( $request_data['verso_ord'] == 'desc' ){
                $responseData['verso_ord']           = 'asc';
                $responseData['forn_ord_campo_corr'] = $request_data['forn_ord_campo'];
                $verso_ord                            = 'desc';
            } else {
                $responseData['verso_ord']           = 'desc';
                $responseData['forn_ord_campo_corr'] = $request_data['forn_ord_campo'];
                $verso_ord                            = 'asc';
            }
            $forn_ord_campo = $request_data['forn_ord_campo'];
        } else {
            if ( !isset($request_data['verso_ord']) ) {
                $responseData['verso_ord'] = 'desc';
                $verso_ord                  = 'asc';
            } else {
                if($this->clear_pars['verso_ord'] == 'desc'){
                    $responseData['verso_ord'] = 'asc';
                    $verso_ord                  = 'desc';
                } else {
                    $responseData['verso_ord'] = 'desc';
                    $verso_ord                  = 'asc';
                }
            }
            $forn_ord_campo                       = '';
            $responseData['forn_ord_campo_corr'] = 'TIPO_CONC';
        }

        $arrayBind = array(
            'anno_in'              => $dett_anno_r,
            'semestre_in'          => $dett_semestre_r,
            'tipodett_in'          => $tipodett,
            'pagina_in'            => $pagina,
            'num_res_in'           => $paging,
            'campo_ordinamento_in' => $forn_ord_campo,
            'verso_ordinamento_in' => $verso_ord,
            'cfaamsIn'             => $cf,
           );
        $res = $this->db->calcolaStatDett($arrayBind);
        if ($this->db->getError() != "") {
            $responseData['messaggio'] = 'Disallineamento tabella dettaglio';
        } else if ($retVal == null) {
            $responseData['messaggio'] = 'Nessun concessionario per i parametri selezionati';
        } else {
            $responseData['dati_post']           = $this->clear_pars;

            $responseData['show_ancora']         = 1;
            $totale_parziale                      = count($res);

            $totale_generale                      = $res[0]['ROWS_TOT'];
            $totConcDist                          = $res[0]['ROWS_TOT2'];
            $nButton                              = ceil(($totale_generale / $paging));
            $responseData['elenco']['risultati'] = $res;
            $responseData['elenco']['pagestot']  = $nButton + 1;
            $responseData['starting_from']       = ($paging * $pagina - ($paging - 1) -1);// Valore del counter start nel tpl
            $responseData['table_caption']       = 'Anno: ' . $dett_anno_r .
                                                    ' Semestre: '.$dett_semestre_r.'<br>' .
                                                    'Elenco Concessionari ';
            $responseData['dett_anno_r']         = $dett_anno_r;
            $responseData['dett_semestre_r']     = $dett_semestre_r;
            $responseData['dettlink_r']          = $tipodett;
            
            
            $tipoGiochi = array(
                            'TOT_GP_OK_NON'       =>
                'Giochi Ippici e Sportivi non attivi che hanno trasmesso',
                            'TOT_BINGO_OK_NON'    =>
                'Bingo non attivi che hanno trasmesso',
                            'TOT_GAD_OK_NON'      =>
                'GAD non attivi che hanno trasmesso',
                            'TOT_VID_OK_NON'      =>
                'Apparecchi da intrattenimento non attivi che hanno trasmesso',
                            'TOT_OK_NON'          =>
                'non attivi che hanno trasmesso',
                            'TOT_GP_OK_DWH_OK'    =>
                'Giochi Ippici e Sportivi che hanno trasmesso dati coincidenti con DataWareHouse',
                            'TOT_BINGO_OK_DWH_OK' =>
                'Bingo che hanno trasmesso dati coincidenti con DataWareHouse',
                            'TOT_GAD_OK_DWH_OK'   =>
                'GAD che hanno trasmesso dati coincidenti con DataWareHouse',
                            'TOT_VID_OK_DWH_OK'   =>
                'Apparecchi da intrattenimento che hanno trasmesso dati coincidenti con DataWareHouse',
                            'TOT_OK_DWH_OK'       =>
                'attivi che hanno trasmesso dati coincidenti con DataWareHouse',
                            'TOT_GP_OK_DWH_KO'    =>
                'Giochi Ippici e Sportivi che hanno trasmesso dati diversi da DataWareHouse',
                            'TOT_BINGO_OK_DWH_KO' =>
                'Bingo che hanno trasmesso dati diversi da DataWareHouse',
                            'TOT_GAD_OK_DWH_KO'   =>
                'GAD che hanno trasmesso dati diversi da DataWareHouse',
                            'TOT_VID_OK_DWH_KO'   =>
                'Apparecchi da intrattenimento che hanno trasmesso dati diversi da DataWareHouse',
                            'TOT_OK_DWH_KO'       =>
                'che hanno trasmesso dati diversi da DataWareHouse',
                            'TOT_GP_OK'           =>
                'Giochi Ippici e Sportivi che hanno trasmesso',
                            'TOT_BINGO_OK'        =>
                'Bingo che hanno trasmesso',
                            'TOT_GAD_OK'          =>
                'GAD che hanno trasmesso',
                            'TOT_VID_OK'          =>
                'Apparecchi da intrattenimento che hanno trasmesso',
                            'TOT_OK'              =>
                'che hanno trasmesso',
                            'TOT_GP_KO'           =>
                'Giochi Ippici e Sportivi inadempienti',
                            'TOT_BINGO_KO'        =>
                'Bingo inadempienti',
                            'TOT_GAD_KO'          =>
                'GAD inadempienti',
                            'TOT_VID_KO'          =>
                'Apparecchi da intrattenimento inadempienti',
                            'TOT_KO'              =>
                'inadempienti',
                            'TOT_GP_CONC'         =>
                'Giochi Ippici e Sportivi attivi',
                            'TOT_BINGO_CONC'      =>
                'Bingo attivi',
                            'TOT_GAD_CONC'        =>
                'GAD attivi',
                            'TOT_VID_CONC'        =>
                'Apparecchi da intrattenimento attivi',
                            'TOT_CONC'            =>
                'attivi',
                );

                $responseData['table_caption']      .= $tipoGiochi[$tipodett].
                '<br>TOTALE CONCESSIONARI: '.$totConcDist;
        }

        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il reperimento del dettaglio"];
            return $response->withJson($data, 500);
        }
        
    }

    function getCSV1Dett(Request $request, Response $response) 
    {
        $request_data = $request->getParsedBody();
        $dettLinkAnno             = $request_data['dettLinkAnno'];
        $dettLinkSem         = $request_data['dettLinkSem'];
        $dettlink         = $request_data['dettlink'];
        $cf_utente         = $request_data['cf_utente'];

        $arrayBind             = array(
                                  'anno_in'              => $dett_anno_r,
                                  'semestre_in'          => $dett_semestre_r,
                                  'tipodett_in'          => $tipodett,
                                  'pagina_in'            => null,
                                  'num_res_in'           => null,
                                  'campo_ordinamento_in' => null,
                                  'verso_ordinamento_in' => null,
                                  'cfaamsIn'             => $cf,
                                 );
        $res = $this->db->calcolaStatDett($arrayBind);
        if ($this->db->getError() != "") {
            $responseData['messaggio'] = 'Disallineamento tabella dettaglio';
        } else if ($retVal == null) {
            $responseData['messaggio'] = 'Nessun concessionario per i parametri selezionati';
        } 
        else 
        {
            $campi                        = array(
                'TIPO_CONC'                    => 'TIPO CONCESSIONE',
                'COD_CONC'                     => 'CODICE CONCESSIONE',
                'RAG_SOC'                      => 'RAGIONE SOCIALE',
                'STATO'                        => 'STATO',
                'DATA_STIPULA'                 => 'DATA STIPULA',
                'DATA_FINE'                    => 'DATA FINE',
                'TELEFONO'                     => 'TELEFONO',
                'EMAIL'                        => 'EMAIL',
                'DT_TRASMISSIONE'              => 'DATA TRASMISSIONE'
               );
                $res                          = $this->_trasposizioneMatrice($res, $campi);

                $tipoGiochi                   = array(
                                'TOT_GP_OK_NON'       => 'Giochi Ippici e Sportivi non attivi che hanno trasmesso dati coincidenti con DataWareHouse',
                                'TOT_BINGO_OK_NON'    => 'Bingo non attivi che hanno trasmesso dati coincidenti con DataWareHouse',
                                'TOT_GAD_OK_NON'      => 'GAD non attivi che hanno trasmesso dati coincidenti con DataWareHouse',
                                'TOT_VID_OK_NON'      => 'Apparecchi da intrattenimento non attivi che hanno trasmesso dati coincidenti con DataWareHouse',
                                'TOT_OK_NON'          => 'non attivi che hanno trasmesso dati coincidenti con DataWareHouse',
                                'TOT_GP_KO_NON'       => 'Giochi Ippici e Sportivi non attivi che hanno trasmesso dati diversi da DataWareHouse',
                                'TOT_BINGO_KO_NON'    => 'Bingo non attivi che hanno trasmesso dati diversi da DataWareHouse',
                                'TOT_GAD_KO_NON'      => 'GAD non attivi che hanno trasmesso dati diversi da DataWareHouse',
                                'TOT_VID_KO_NON'      => 'Apparecchi da intrattenimento non attivi che hanno trasmesso dati diversi da DataWareHouse',
                                'TOT_KO_NON'          => 'non attivi che hanno trasmesso dati diversi da DataWareHouse',
                                'TOT_GP_OK_DWH_OK'    => 'Giochi Ippici e Sportivi che hanno trasmesso dati coincidenti con DataWareHouse',
                                'TOT_BINGO_OK_DWH_OK' => 'Bingo che hanno trasmesso dati coincidenti con DataWareHouse',
                                'TOT_GAD_OK_DWH_OK'   => 'GAD che hanno trasmesso dati coincidenti con DataWareHouse',
                                'TOT_VID_OK_DWH_OK'   => 'Apparecchi da intrattenimento che hanno trasmesso dati coincidenti con DataWareHouse',
                                'TOT_OK_DWH_OK'       => 'attivi che hanno trasmesso dati coincidenti con DataWareHouse',
                                'TOT_GP_OK_DWH_KO'    => 'Giochi Ippici e Sportivi che hanno trasmesso dati diversi da DataWareHouse',
                                'TOT_BINGO_OK_DWH_KO' => 'Bingo che hanno trasmesso dati diversi da DataWareHouse',
                                'TOT_GAD_OK_DWH_KO'   => 'GAD che hanno trasmesso dati diversi da DataWareHouse',
                                'TOT_VID_OK_DWH_KO'   => 'Apparecchi da intrattenimento che hanno trasmesso dati diversi da DataWareHouse',
                                'TOT_OK_DWH_KO'       => 'che hanno trasmesso dati diversi da DataWareHouse',
                                'TOT_GP_OK'           => 'Giochi Ippici e Sportivi che hanno trasmesso',
                                'TOT_BINGO_OK'        => 'Bingo che hanno trasmesso',
                                'TOT_GAD_OK'          => 'GAD che hanno trasmesso',
                                'TOT_VID_OK'          => 'Apparecchi da intrattenimento che hanno trasmesso',
                                'TOT_OK'              => 'che hanno trasmesso',
                                'TOT_GP_KO'           => 'Giochi Ippici e Sportivi inadempienti',
                                'TOT_BINGO_KO'        => 'Bingo inadempienti',
                                'TOT_GAD_KO'          => 'GAD inadempienti',
                                'TOT_VID_KO'          => 'Apparecchi da intrattenimento inadempienti',
                                'TOT_KO'              => 'inadempienti',
                                'TOT_GP_CONC'         => 'Giochi Ippici e Sportivi attivi',
                                'TOT_BINGO_CONC'      => 'Bingo attivi',
                                'TOT_GAD_CONC'        => 'GAD attivi',
                                'TOT_VID_CONC'        => 'Apparecchi da intrattenimento attivi',
                                'TOT_CONC'            => 'attivi',
                            );
                $responseData['show_ancora'] = 1;

                $responseData['elenco']  = $res;
                $responseData['sezione'] = 'Rapporto Concessorio Antiriciclaggio - Monitoraggio\nn'.
                            " ANNO: $dett_anno_r SEMESTRE: $dett_semestre_r \n\n".
                            'Elenco Concessionari '.$tipoGiochi[$tipodett].'\n';
                $responseData['tpl_name']           = 'Rapporto Concessorio Antiriciclaggio - Monitoraggio';           
        }

        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il reperimento del dettaglio"];
            return $response->withJson($data, 500);
        }

    }

    function getResultNew()
    {
        $request_data = $request->getParsedBody();
        $anno             = $request_data['anno'];
        $semestre         = $request_data['semestre'];
        $tipoconc         = $request_data['tipoconc'];
        $tipogioco        = $request_data['tipogioco']; 
        $tiporete         = $request_data['tiporete'] ;
        $tipoDettGP            = array(
                                  'AI',
                                  'AS',
                                  'I',
                                  'IDLG',
                                  'IPP',
                                  'S',
                                  'X',
                                  'A',
                                 );
        if ($tipo_conc == 'B') {
            $tipo_dett_in = 'TOT_BINGO_KO';
        } elseif ($tipo_conc == 'GAD') {
            $tipo_dett_in = 'TOT_GAD_KO';
        } elseif ($tipo_conc == 'VID') {
            $tipo_dett_in = 'TOT_VID_KO';
        } elseif (in_array($tipo_conc, $tipoDettGP)) {
            $tipo_dett_in = 'TOT_GP_KO';
        } else {
            $tipo_dett_in = null;
        }

        $responseData['anno']      = $anno;
        $responseData['semestre']  = $semestre;
        $responseData['tipo_conc'] = $tipo_conc;
        $responseData['tipogioco'] = $tipogioco;
        $cf                         = $request_data['cf_utente'];
        $responseData['tiporete']  = $tiporete;

        /*
            If ($this->checkGioco($tipo_conc, $tipogioco, $anno) == 0 ||
            $this->controllaPeriodoConcessione($tipo_conc, $semestre, $anno,
            $tiporete, $tipogioco) == 0 || !$this->vincoli($tipo_conc, $anno,
            $semestre, $tiporete)) {
            *
        */

        if ($tipogioco == -1) {
            $codGioco = null;
        } else {
            $codGioco = $tipogioco;
        }

        if ($semestre == -1) {
            $sem = null;
        } else {
            $sem = $semestre;
        }

        $pars = array(
                 'tipo_conc' => $tipo_conc,
                 'tipo_rete' => $tiporete,
                 'cod_gioco' => $codGioco,
                 'anno'      => $anno,
                 'semestre'  => $sem,
                );
        if (!$this->_selGiochi($pars) ||
        $this->_controllaPeriodoConcessione($tipo_conc, $semestre, $anno,
        $tiporete, $tipogioco) == 0) {
            $responseData['messaggio'] = 'Dati di ricerca incongruenti';
            return -1;
        } elseif (
                $this->_controllaPeriodoConcessione($tipo_conc, $semestre,
        $anno, $tiporete, $tipogioco) == -1) {
            $responseData['messaggio'] =
            'Non si possono effettuare ricerche per periodi non conclusi';
            return -1;
        }

        $paging = 40;
        if (isset($request_data['pagina']) && $request_data['pagina']) {
            $pagina = $request_data['pagina'];
        } else {
            $pagina                     = 1;
            $responseData['pagina'] = $pagina;
        }

        // Salvo numero pagina in caso di richiesta CSV.
        if ($pagina != null && $pagina != '') {
            $responseData['paginaCSV'] = $pagina;
        }

        $arrayBind = array(
                      'tipodett_in'  => $tipo_dett_in,
                      'tipo_conc_in' => $tipo_conc,
                      'anno_in'      => $anno,
                      'semestre_in'  => $semestre,
                      'cod_gioco_in' => $tipogioco,
                      'tipo_rete_in' => $tiporete,
                      'cfaams_in'    => $cf,
                      'pagina_in'    => $pagina,
                      'num_res_in'   => $paging,
                     );
        $res = $this->db->selElencoInadempientiNew($arrayBind);
        if ($this->db->getError() != "") {
            $responseData['messaggio'] = 'Errore procedura selElencoInadempientiNew';
        } else if ($retVal == null) {
            $responseData['messaggio'] = 'Nessun concessionario inadempiente per i parametri selezionati';
        } 
        else 
        {         
            $nConc                        = $res['ROWS_TOT2'][0];
            $nRows                        = $res['ROWS_TOT'][0];
            $nButton                      = ceil(($nRows / $paging));
            $responseData['dati_post']   = $this->clear_pars;
            $responseData['show_ancora'] = 1;
            $responseData['tot_result']  = $this->db->num_cn;
            $res['pagestot']              = $nButton + 1;
            $responseData['elenco']      = $res;
            
        }       

        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il reperimento del dettaglio"];
            return $response->withJson($data, 500);
        }

    }

    function getCSVNew()
    {
        $request_data = $request->getParsedBody();
        $anno             = $request_data['anno'];
        $semestre         = $request_data['semestre'];
        $tipoconc         = $request_data['tipoconc'];
        $tipogioco        = $request_data['tipogioco']; 
        $tiporete         = $request_data['tiporete'] ;


        $responseData['anno']      = $anno;
        $responseData['semestre']  = $semestre;
        $responseData['tipoconc']  = $tipoconc;
        $responseData['tipogioco'] = $tipogioco;
        $responseData['tiporete'] = $tiporete;

        $tipoDettGP            = array(
                                  'AI',
                                  'AS',
                                  'I',
                                  'IDLG',
                                  'IPP',
                                  'S',
                                  'X',
                                  'A',
                                 );
        if ($tipo_conc == 'B') {
            $tipo_dett_in = 'TOT_BINGO_KO';
        } elseif ($tipo_conc == 'GAD') {
            $tipo_dett_in = 'TOT_GAD_KO';
        } elseif ($tipo_conc == 'VID') {
            $tipo_dett_in = 'TOT_VID_KO';
        } elseif (in_array($tipo_conc, $tipoDettGP)) {
            $tipo_dett_in = 'TOT_GP_KO';
        } else {
            $tipo_dett_in = null;
        }

        $responseData['anno']      = $anno;
        $responseData['semestre']  = $semestre;
        $responseData['tipo_conc'] = $tipo_conc;
        $responseData['tipogioco'] = $tipogioco;
        $responseData['tiporete']  = $tiporete;
        if (isset($request_data['paginaCSV']) &&
        $request_data['paginaCSV']) {
            $pagina = $request_data['paginaCSV'];
        } else {
            $pagina                        = 1;
            $responseData['paginaCSV'] = $pagina;
        }

        $paging    = 40;
        $arrayBind = array(
                      'tipodett_in'  => $tipo_dett_in,
                      'tipo_conc_in' => $tipo_conc,
                      'anno_in'      => $anno,
                      'semestre_in'  => $semestre,
                      'cod_gioco_in' => $tipogioco,
                      'tipo_rete_in' => $tiporete,
                      'cfaams_in'    => '',
                      'pagina_in'    => null,
                      'num_res_in'   => null,
                     );
        $res = $this->db->selElencoInadempientiNew($arrayBind);
        if ($this->db->getError() != "") {
            $responseData['messaggio'] = 'Errore procedura selElencoInadempientiNew';
        } else if ($retVal == null) {
            $responseData['messaggio'] = 'Nessun concessionario inadempiente per i parametri selezionati';
        } 
        else 
        {         
            if ($tipogioco != -1) {
                $descr_gioco                       = $res[0]['DESCR_GIOCO'];
                $responseData['tipo_concessione'] = $res[0]['DESCR'];
                $campi                             = array(
                                                      'COD_CONC'  =>
                    'Codice concessione',
                                                      'RAG_SOC'   =>
                    'Ragione Sociale',
                                                      'DATA_CONC' =>
                    'Data stipula concessione',
                                                     );
            } 
            else 
            {
                $descr_gioco                       = 'TUTTI';
                $responseData['tipo_concessione'] = $res[0]['DESCR'];
                $campi                             = array(
                                                      'COD_CONC'    => 'Codice concessione',
                                                      'RAG_SOC'     => 'Ragione Sociale',
                                                      'DATA_CONC'   => 'Data stipula concessione',
                                                      'DESCR_GIOCO' => 'Tipo gioco non trasmesso',
                                                     );
            }

            if ($tipo_conc == 'VID') {
                if (is_array($res) && count($res)) {
                    foreach($res as $k => $v) {
                        $res[$k]['COD_CONC'] = 'ND';
                    }
                }
            }

            $res                     = $this->_trasposizioneMatrice($res, $campi);
            $responseData['elenco'] = $res;
            if ($tipo_conc != 'GAD') {
                if ($tiporete == 'F') {
                    $tiporeteSTR = '\n Tipo raccolta fisica \n';
                } else {
                    $tiporeteSTR = '\n Tipo raccolta a distanza \n';
                }
            } else {
                $tiporeteSTR = '\n';
            }

            if ($semestre == 1) {
                $semestreSTR = ' primo semestre';
            } else {
                $semestreSTR = ' secondo semestre';
            }

            $annosemestre              = ' \n anno '.$anno.$semestreSTR;
            $responseData['sezione']  =
            'Antiriciclaggio - Elenco concessionari inadempienti ';
            $responseData['sezione'] .=
            $responseData['tipo_concessione'].'\n\n  '.$annosemestre;
            $responseData['sezione'] .= $tiporeteSTR.'Tipo gioco ';
            $responseData['sezione'] .= $descr_gioco;
            $responseData['tpl_name'] = 'Monitoraggio ';
            $responseData['tpl_name'] .= $this->tpl_dat['tipo_concessione'];
            
        }    
        
        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il reperimento del dettaglio"];
            return $response->withJson($data, 500);
        }
 
    }
    //FINE --- RC01_antiric_monitoraggio.inc 



}


    /*
	function controlla_valori1(Request $request, Response $response)
    {
        $result        = true;
		$tpl_dat       = "";
		$request_data  = $request->getParsedBody();
        $concessionario = $request_data['concessionario'];
		$tipo_conc = $request_data['tipo_conc'];
		$num_provv = $request_data['num_provv'];
		$data_provv = $request_data['data_provv'];		
		$tipo_provv = $request_data['tipo_provv'];	



		
        //$this->clear_pars['concessionario'] = 'aa';
        if ((isset($concessionario) && 
                $concessionario != '')){
            if (!is_numeric($concessionario)){
                $result = false;
                $tpl_dat = 'Valore codice concessionario non valido';
            }
        }
        //$this->clear_pars['tipo_conc'] = 'aaa';
        if ((isset($tipo_conc) && 
                $tipo_conc != '') && $result == true){
            if ($tipo_conc != 'A' && $tipo_conc != 'S'
                && $tipo_conc != 'I' && $tipo_conc != 'GAD' 
                && $tipo_conc != 'X') {
                $result = false;
                $tpl_dat = 'Valore tipo concessione non valido';
            }  
        }
        if ((isset($num_provv) && 
                $num_provv != '') && $result == true){
            if (!is_numeric($num_provv)){
                $result = false;
                $tpl_dat = 'Valore numero provvedimento adm non valido';
            }  
        }
        
        if ((isset($data_provv) && 
                $data_provv != '') && $result == true){
            $giorno = substr($data_provv,0,2);
            $mese = substr($data_provv,3,2);
            $anno = substr($data_provv,6,4);
            
            if (strlen($data_provv) == 10) {
                if (checkdate($mese,$giorno,$anno) == false) {
                    $result = false;
                    $tpl_dat = 'Valore data provvedimento adm non valido';
                }
            }else {
                $result = false;
                $tpl_dat = 'Valore data provvedimento adm non valido';
            }
        }
        if ((isset($tipo_provv) && 
                $tipo_provv != '') && $result == true){
            if ($tipo_provv != 'RTI' && $tipo_provv != 'RTS'
                && $tipo_provv != 'RTX' && $tipo_provv != 'RTA' 
                && $tipo_provv != 'D' && $tipo_provv != 'DD' && $tipo_provv != 'R') {
                $result = false;
                $tpl_dat = 'Valore tipo provvedimento non valido';
            }  
        }
        
        $responseData['tpl_dat'] = $tpl_dat;
        $responseData['result'] = $result;
		
		
        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il controllo dei valori1"];
            return $response->withJson($data, 500);
        }
    }
	
	
	 function controlla_valori2(Request $request, Response $response)
    {
        $result        = true;
		$tpl_dat       = "";
		$request_data  = $request->getParsedBody();
        $numero_cont = $request_data['numero_cont'];
		$anno_cont = $request_data['anno_cont'];
		$num_provv = $request_data['codice_cont'];

        
        
        
        if ((isset($numero_cont) && 
                $numero_cont != '') && $result == true){
            if (strlen($numero_cont) < 9) {
                if (!is_numeric($numero_cont)){
                    $result = false;
                    $tpl_dat = 'Valore numero contenzioso non valido';
                }
            }else{
                $result = false;
                $tpl_dat = 'Valore numero contenzioso non valido';
            }
        }
        if ((isset($anno_cont) && 
                $anno_cont != '') && $result == true){
            if (strlen($anno_cont) < 5) {
                if (!is_numeric($anno_cont)){
                    $result = false;
                    $tpl_dat = 'Valore numero contenzioso non valido';
                }
            }else{
                $result = false;
                $tpl_dat = 'Valore numero contenzioso non valido';
            }
        }
        if ((isset($num_provv) && 
                $num_provv] != '') && $result == true){
            if (strlen($num_provv) == 1) {
                if (!is_numeric($num_provv)){
                    $result = false;
                    $tpl_dat = 'Valore codice contenzioso non valido';
                }
            }else{
                $result = false;
                $tpl_dat = 'Valore codice contenzioso non valido';
            }
        }
		
		
        $responseData['tpl_dat'] = $tpl_dat;
        $responseData['result'] = $result;
		
		
        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il controllo dei valori2"];
            return $response->withJson($data, 500);
        }
    }


	 function controlla_valori3(Request $request, Response $response)
    {
        $result        = true;
		$tpl_dat       = "";
		$request_data  = $request->getParsedBody();
        $cod_provv_giud = $request_data['cod_provv_giud'];
		$num_provv_giud = $request_data['num_provv_giud'];
		$data_provv_giud = $request_data['data_provv_giud'];
		$dt_dec_prov_giud = $request_data['dt_dec_prov_giud'];
	    $esito = $request_data['esito'];
        
        if ((isset($cod_provv_giud ) && 
                $cod_provv_giud  != '') && $result == true){
            if (strlen($cod_provv_giud ) == 1) {
                if (!is_numeric($cod_provv_giud )){
                    $result = false;
                    $tpl_dat = 'Valore codice provvedimento giudiziario non valido';
                }
            }else{
                $result = false;
                $tpl_dat = 'Valore codice provvedimento giudiziario non valido';
            }
        }
        

        if ((isset($num_provv_giud) && 
                $num_provv_giud != '') && $result == true){
            if (strlen($num_provv_giud) < 9) {
                if (!is_numeric($num_provv_giud)){
                    $result = false;
                    $tpl_dat = 'Valore numero provvedimento giudiziario non valido';
                }
            }else{
                    $result = false;
                    $tpl_dat = 'Valore numero provvedimento giudiziario non valido';
            }
            
        }
        if ((isset($data_provv_giud) && 
                $data_provv_giud != '') && $result == true){
            $giorno = substr($data_provv_giud,0,2);
            $mese = substr($data_provv_giud,3,2);
            $anno = substr($data_provv_giud,6,4);
            if (strlen($data_provv_giud) == 10) {
                if (checkdate($mese,$giorno,$anno) == false) {
                    $result = false;
                    tpl_dat =  'Valore data provvedimento giudiziario non valido';
                }
            }else {
                $result = false;
                tpl_dat = 'Valore data provvedimento giudiziario non valido';
            }
        }
        
        if ((isset($dt_dec_prov_giud) && 
                $dt_dec_prov_giud != '') && $result == true){
            $giorno = substr($dt_dec_prov_giud,0,2);
            $mese = substr($dt_dec_prov_giud,3,2);
            $anno = substr($dt_dec_prov_giud,6,4);
            if (strlen($dt_dec_prov_giud) == 10) {
                if (checkdate($mese,$giorno,$anno) == false) {
                    $result = false;
                    tpl_dat =  'Valore data decorrenza provvedimento giudiziario non valido';
                }
            }else {
                $result = false;
                tpl_dat =  'Valore data decorrenza provvedimento giudiziario non valido';
            }
        }
        
        if ((isset($esito) && 
                $esito != '') && $result == true){
            if (strlen($esito) == 1 ) {
                if (!is_numeric($esito)){
                    $result = false;
                    $esito = 'Valore esito del provvedimento giudiziario non valido';
                }
            }else{
                    $result = false;
                    $esito = 'Valore esito del provvedimento giudiziario non valido';
            }
            
        }
		
		
        $responseData['tpl_dat'] = $tpl_dat;
        $responseData['result'] = $result;
		
		
        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il controllo dei valori3"];
            return $response->withJson($data, 500);
        }
    }


	function CaricaDati(Request $request, Response $response)
	{
		
		$request_data  = $request->getParsedBody();
        $tipoconc = $request_data['tipoconc'];
		$codconc = $request_data['codconc'];
		$coddir = $request_data['coddir'];
		$ragsoc = $request_data['ragsoc'];
	    $tipodecad = $request_data['tipodecad'];
	    $coddecad = $request_data['coddecad'];
	    $datadecad = $request_data['datadecad'];
	    $dataall = $request_data['dataall'];
	    $stat = $request_data['stat'];
	    $cumu = $request_data['cumu'];
		
	    $responseData['tipoconc'] = $tipoconc;
        $responseData['codconc'] = $codconc;
        $responseData['coddir'] = $coddir;
        $responseData['ragsoc'] = $ragsoc;
        $responseData['tipodecad'] = $tipodecad;
        $responseData['coddecad'] = $coddecad;
        $responseData['datadecad'] = $datadecad;
        $responseData['dataall'] = $dataall;
        $responseData['stat'] = $stat;
        $responseData['cumu'] = $cumu;
		
        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il caricamento dei dati"];
            return $response->withJson($data, 500);
        }	
	}

	function checkFields(Request $request, Response $response)
	{
		$request_data  = $request->getParsedBody();
        $giorno = $request_data['giorno'];
		$mese = $request_data['mese'];
		$anno = $request_data['anno'];
		$giorno_provv = $request_data['giorno_provv'];
	    $mese_provv = $request_data['mese_provv'];
	    $anno_provv = $request_data['anno_provv'];
	    $num_provv = $request_data['num_provv'];
	    $diritto = $request_data['diritto'];
	    $messaggio = $request_data['messaggio'];

		
		
		$flag_check = 0;
		if(strlen($mese.$giorno.$anno) != 8)
		{
			$flag_check = 1;
			$messaggio = "<FONT style='font-size: 14pt'>Data di decorrenza decadenza obbligatoria per il diritto ".$diritto."<br />";
		}
		elseif($mese and $giorno and $anno
		and $mese != "" and $giorno != "" and $anno != ""
		and !checkdate($mese, $giorno, $anno))
		{
			$flag_check = 1;
			$messaggio = "<FONT style='font-size: 14pt'>Data di decorrenza decadenza errata per il diritto ".$diritto."<br />";
		}
		elseif(date("Ymd") >=$anno.$mese.$giorno)
		{
			//controllo data decadenza > data di sistema KO ricarico lista
			$flag_check = 1;
			$messaggio = "<FONT style='font-size: 14pt'>Data di decorrenza deve essere maggiore data odierna per il diritto ".$diritto."<br />";
		}
		elseif(strlen($mese_provv.$giorno_provv.$anno_provv) != 8)
		{
			$flag_check = 1;
			$messaggio = "<FONT style='font-size: 14pt'>Data del provvedimento obbligatoria per il diritto ".$diritto."<br />";
		}
		elseif($mese_provv and $giorno_provv and $anno_provv
		and $mese_provv != "" and $giorno_provv != "" and $anno_provv != ""
		and !checkdate($mese_provv, $giorno_provv, $anno_provv))
		{
			$flag_check = 1;
			$messaggio = "<FONT style='font-size: 14pt'>Data del provvedimento errata per il diritto ".$diritto."<br />";
		}
		elseif(date("Ymd") <$anno_provv.$mese_provv.$giorno_provv)
		{
			//controllo data provvedimento < data di sistema KO ricarico lista
			$flag_check = 1;
			$messaggio = "<FONT style='font-size: 14pt'>Data del provvedimento deve essere minore data odierna per il diritto ".$diritto."<br />";
		}
		elseif ($num_provv =='')
        {
  	        //controllo numero provvedimento obbligatorio
			$flag_check = 1;
			$messaggio = "<FONT style='font-size: 14pt'>Numero del provvedimento obbligatorio per il diritto ".$diritto."<br />";
        }
        elseif (!is_numeric($num_provv))
		{
		    //controllo numero provvedimento numerico
			$flag_check = 1;
	   		$messaggio = "<FONT style='font-size: 14pt'>Numero del provvedimento deve essere numerico per il diritto ".$diritto."<br />";
		} 
		
        $responseData['flag_check'] = $flag_check;
        $responseData['messaggio'] = $messaggio;
		
		
        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il check fields"];
            return $response->withJson($data, 500);
        }
    }
    */
