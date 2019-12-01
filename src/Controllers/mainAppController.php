<?php

namespace NIM_Backend\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
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

        if (isset($request_data) && array_key_exists('F', $request_data) && $request_data['F'] == 'Raccolta fisica') {
            $tipoRetePuntuale = 'F';
        } else if (isset($request_data) && array_key_exists('D', $request_data) && $request_data['D'] == 'Raccolta a distanza') {
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
            $retVal = $this->db->selGiochi($pars);
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
            return $this->db->selGiochi($pars);
        }else{
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
}
