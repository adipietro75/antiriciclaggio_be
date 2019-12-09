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
                        $giochi = $this->_calcolaGiochi2($tipo_conc, $traccolta);
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
                        $giochi = $this->_calcolaGiochi3($tipo_conc, $gioco);
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
                    $anni = $this->_calcolaAnni2($responseData,$res);
                } else {
                    $anni = $this->_calcolaAnni3($responseData,$res, $anno, $semestre);
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

    function _mappaturaAnni($responseData,$res)
    {
        $count = 0;
        $anni  = array();
        foreach ($res as $value) {
            $search = $value['ANNO'].$value['SEMESTRE'];
            if (array_search($search, $anni) === false) {
                $anni[$search] = $search;
                $count++;
            }
        }

        if ($anni != null) {
            $responseData['mappaturaAnni'] = $anni;
        }
    }

    function _calcolaAnni2($responseData,$res)
    {
        $this->_mappaturaAnni($responseData,$res);
        $anno                          = date('y');
        $mese                          = date('m');
        $this->tpl_dat['annoCorrente'] = '20'.$anno;
        if ($mese <= 6) {
            $responseData['semestreCorrente'] = 1;
            $anno--;
        } else if ($mese >= 7) {
            $responseData['semestreCorrente'] = 2;
        }

        $data = null;
        if ($res != null && $res[0] != null && array_key_exists('DATA_CONC',
        $res[0])) {
            $data = $res[0]['DATA_CONC'];
        }

        if ($data != null) {
            $stipulaAnno     = substr($data, strlen($data) - 2, strlen($data));
            $stipulaSemestre = substr($data, strlen($data) - 7, 2);
            if ($stipulaSemestre > '06') {
                $stipulaSemestre = 1;
            } else {
                $stipulaAnno--;
                $stipulaSemestre = 2;
            }
        }

        $annoPartenza = '11';
        if (isset($stipulaAnno) && $stipulaAnno != null && $stipulaAnno > 9) {
            $stipulaAnno = '20'.$stipulaAnno;
        } elseif (isset($stipulaAnno) && $stipulaAnno != null &&
        $stipulaAnno < 10) {
            $stipulaAnno = '20'.$stipulaAnno;
        }

        if (isset($stipulaSemestre)) {
            $responseData['semestreStipula'] = $stipulaSemestre;
        }

        if (isset($stipulaAnno)) {
            $responseData['annoStipula'] = $stipulaAnno;
        }

        $arrAnno = array();
        $count   = 0;
        while ($annoPartenza <= $anno) {
            if ($annoPartenza > 9) {
                $arrAnno[$count] = '20'.$annoPartenza;
            } else {
                $arrAnno[$count] = '200'.$annoPartenza;
            }

            $count++;
            $annoPartenza++;
        }

        return $arrAnno;
    }


    function _calcolaAnni3($responseData,$res, $anno, $semestre)
    {
        $responseData['tipoanno'] = 'S';
        $responseData['annoOggi'] = date('Y');
        $this->_mappaturaAnni($responseData,$res);
        $anno                          = substr($anno, 2, 3);
        $mese                          = date('m');
        $responseData['annoCorrente'] = '20'.$anno;
        if ($mese <= 6) {
            $responseData['semestreCorrente'] = 1;
        } elseif ($mese >= 7) {
            $responseData['semestreCorrente'] = 2;
        }

        $data = null;
        if ($res != null && $res[0] != null) {
            $data = isset($res[0]['DATA_CONC']) ? $res[0]['DATA_CONC'] : null;
        }

        if ($data != null) {
            $stipulaAnno     = substr($data, strlen($data) - 2, strlen($data));
            $stipulaSemestre =
            substr($data, strlen($data) - 7, strlen($data) - 5);
            if ($stipulaSemestre > 6) {
                $stipulaSemestre = 1;
            } else {
                $stipulaSemestre = 2;
                $stipulaAnno--;
            }
        }

        if ($anno == 11 && ($semestre == 1 || $semestre == -1)) {
            $annoPartenza     = '11';
            $semestrePartenza = 1;
        } else {
            $annoPartenza = $anno;
            if ($semestre != 2) {
                $semestrePartenza = 1;
            } else {
                $semestrePartenza = 2;
            }
        }

        if (isset($stipulaAnno) && $stipulaAnno != null && $stipulaAnno > 9) {
            $stipulaAnno = '20'.$stipulaAnno;
        } elseif (isset($stipulaAnno) && $stipulaAnno != null &&
        $stipulaAnno < 10) {
            $stipulaAnno = '20'.$stipulaAnno;
        }

        $responseData['semestreStipula'] =
        isset($stipulaSemestre) ? $stipulaSemestre : null;
        $responseData['annoStipula']     =
        isset($stipulaAnno) ? $stipulaAnno : null;
        $arrAnno                          = array();
        $count                            = 0;
        while ($annoPartenza <= $anno) {
            if ($annoPartenza > 9) {
                $arrAnno[$count] = '20'.$annoPartenza;
            } else {
                $arrAnno[$count] = '200'.$annoPartenza;
            }

            $count++;
            $annoPartenza++;
        }

        return $arrAnno;
    }

    function _calcolaGiochi2($tipoconc, $tiporete)
    {
        $giochi['COD_GIOCO'] = array();
        switch ($tipoconc) {
            case 'S':
                $giochi['COD_GIOCO'][0]   = 6;
                $giochi['DESCR_GIOCO'][0] =
                'SCOMMESSE A QUOTA FISSA NON IPPICHE';
                $giochi['COD_GIOCO'][1]   = 2;
                $giochi['DESCR_GIOCO'][1] =
                'SCOMMESSE A TOTALIZZATORE NON IPPICHE';
                $giochi['COD_GIOCO'][2]   = 5;
                $giochi['DESCR_GIOCO'][2] = 'IPPICA NAZIONALE';
                if ($tiporete != 'F') {
                    $giochi['COD_GIOCO'][3]   = 7;
                    $giochi['DESCR_GIOCO'][3] = "GIOCHI DI ABILITA'";
                }
            break;

            // MOD LP 10/01/2012.
            case 'IDLG':
                $giochi['COD_GIOCO'][0]   = 1;
                $giochi['DESCR_GIOCO'][0] =
                'SCOMMESSE IPPICHE A TOTALIZZATORE E A QUOTA FISSA';
                $giochi['COD_GIOCO'][1]   = 5;
                $giochi['DESCR_GIOCO'][1] = 'IPPICA NAZIONALE';
            break;

            case 'I':
                $giochi['COD_GIOCO'][0]   = 1;
                $giochi['DESCR_GIOCO'][0] =
                'SCOMMESSE IPPICHE A TOTALIZZATORE E A QUOTA FISSA';
                $giochi['COD_GIOCO'][1]   = 2;
                $giochi['DESCR_GIOCO'][1] =
                'SCOMMESSE A TOTALIZZATORE NON IPPICHE';
                $giochi['COD_GIOCO'][2]   = 5;
                $giochi['DESCR_GIOCO'][2] = 'IPPICA NAZIONALE';
                if ($tiporete != 'F') {
                    $giochi['COD_GIOCO'][3]   = 7;
                    $giochi['DESCR_GIOCO'][3] = "GIOCHI DI ABILITA'";
                }
            break;

            case 'AI':
            case 'IPP':
                $giochi['COD_GIOCO'][0]   = 1;
                $giochi['DESCR_GIOCO'][0] =
                'SCOMMESSE IPPICHE A TOTALIZZATORE E A QUOTA FISSA';
            break;

            case 'AS':
                $giochi['COD_GIOCO'][0]   = 6;
                $giochi['DESCR_GIOCO'][0] =
                'SCOMMESSE A QUOTA FISSA NON IPPICHE';
            break;

            case 'B':
                $giochi['COD_GIOCO'][0]   = 8;
                $giochi['DESCR_GIOCO'][0] = 'BINGO';
            break;

            case 'GAD':
                $giochi['COD_GIOCO'][0]   = 1;
                $giochi['DESCR_GIOCO'][0] =
                'SCOMMESSE IPPICHE A TOTALIZZATORE E A QUOTA FISSA';
                $giochi['COD_GIOCO'][1]   = 2;
                $giochi['DESCR_GIOCO'][1] =
                'SCOMMESSE A TOTALIZZATORE NON IPPICHE';
                $giochi['COD_GIOCO'][2]   = 5;
                $giochi['DESCR_GIOCO'][2] = 'IPPICA NAZIONALE';
                $giochi['COD_GIOCO'][4]   = 7;
                $giochi['DESCR_GIOCO'][4] = "GIOCHI DI ABILITA'";
                $giochi['COD_GIOCO'][3]   = 6;
                $giochi['DESCR_GIOCO'][3] = 'SCOMMESSE A QUOTA FISSA NON IPPICHE';
                $giochi['COD_GIOCO'][5]   = 8;
                $giochi['DESCR_GIOCO'][5] = 'BINGO';
                $giochi['COD_GIOCO'][6]   = 0;
                $giochi['DESCR_GIOCO'][6] = 'GIOCO A DISTANZA';
            break;

            case 'VID':
                $giochi['COD_GIOCO'][0]   = 10;
                $giochi['DESCR_GIOCO'][0] = 'VIDEOGIOCHI';
            break;

            case 'X':
            case 'A':
                $giochi['COD_GIOCO'][0]   = 1;
                $giochi['DESCR_GIOCO'][0] =
                'SCOMMESSE IPPICHE A TOTALIZZATORE E A QUOTA FISSA';
                $giochi['COD_GIOCO'][1]   = 2;
                $giochi['DESCR_GIOCO'][1] =
                'SCOMMESSE A TOTALIZZATORE NON IPPICHE';
                $giochi['COD_GIOCO'][2]   = 5;
                $giochi['DESCR_GIOCO'][2] = 'IPPICA NAZIONALE';
                $giochi['COD_GIOCO'][3]   = 6;
                $giochi['DESCR_GIOCO'][3] =
                'SCOMMESSE A QUOTA FISSA NON IPPICHE';
            break;

            default:
                // Azione non prevista.
            break;
        }

        return $giochi;
    }

    function _calcolaGiochi3($tipoconc, $cod_gioco)
    {
        $giochi['COD_GIOCO'] = array();
        switch ($cod_gioco) {
            case 0:
                $giochi['COD_GIOCO'][0]   = 0;
                $giochi['DESCR_GIOCO'][0] = 'GIOCO A DISTANZA';
            break;

            case 1:
                $giochi['COD_GIOCO'][0]   = 1;
                $giochi['DESCR_GIOCO'][0] =
                'SCOMMESSE A QUOTA FISSA NON IPPICHE';
            break;

            case 2:
                $giochi['COD_GIOCO'][0]   = 2;
                $giochi['DESCR_GIOCO'][0] =
                'SCOMMESSE A TOTALIZZATORE NON IPPICHE';
            break;

            case 5:
                $giochi['COD_GIOCO'][0]   = 5;
                $giochi['DESCR_GIOCO'][0] = 'IPPICA NAZIONALE';
            break;

            case 6:
                $giochi['COD_GIOCO'][0]   = 6;
                $giochi['DESCR_GIOCO'][0] =
                'SCOMMESSE A QUOTA FISSA NON IPPICHE';
            break;

            case 7:
                $giochi['COD_GIOCO'][0]   = 7;
                $giochi['DESCR_GIOCO'][0] = "GIOCHI DI ABILITA'";
            break;

            case 8:
                $giochi['COD_GIOCO'][0]   = 8;
                $giochi['DESCR_GIOCO'][0] = 'BINGO';
            break;

            case 10:
                $giochi['COD_GIOCO'][0]   = 10;
                $giochi['DESCR_GIOCO'][0] = 'VIDEOGIOCHI';
            break;

            default:
                // Azione non prevista.
            break;
        }

        return $giochi;
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

        $responseData['tipo_conc']        = $tipoconc;
        $responseData['tiporete']         = $tiporete;
        $responseData['tipogioco']        = $tipogioco;
        $responseData['anno']             = $anno;
        $responseData['semestre']         = $semestre;
        $responseData['tipoconcSelected'] = $tipoconc;

        if (isset($request_data['tipo_conc_visualizza'])) {
            $tipo_conc_visualizza = $request_data['tipo_conc_visualizza'];
        }

        if (isset($request_data['visualizza']) && $request_data['visualizza'] == 'Visualizza>>') {
            $responseData['tipoconc_visualizza'] = $tipoconc;
            $tipo_conc_visualizza                 = $tipoconc;
        } else {
            $this->tpl_dat['tipoconc_visualizza'] = isset($this->clear_pars['tipoconc_visualizza']) ? $this->clear_pars['tipoconc_visualizza'] : null;
        }


        $elencogiochi = $this->_giochiDisponibiliPerConcessioneNew($tipoconc);
        if(!is_array($elencogiochi)) {
            $giochi = $this->_giochiDisponibiliPerConcessioneMonitoraggioInc($tipo_conc_visualizza);
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

        if ($this->_checkGioco($tipoconc, $tipogioco, $anno) == 0 ||
        $this->_controllaPeriodoConcessione($tipoconc, $semestre,
                $anno, $tiporete, $tipogioco) == 0
        || !$this->_vincoli($tipoconc, $anno, $semestre, $tiporete)
        ) {
            $responseData['messaggio'] = 'Dati di ricerca incongruenti';
            return -1;
        } elseif ($this->_controllaPeriodoConcessione($tipoconc, $semestre, $anno, $tiporete, $tipogioco) == -1) {
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
            'tipo_concIn' => $tipoconc,
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
        } else if ($res == null) {
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
            if($tipoconc == 'VID') {
                if(is_array($res) && count($res)) {
                    foreach($res as $k => $v) {
                        $res[$k]['COD_CONC'] = 'ND';
                    }
                }
            }

            $res = $this->_trasposizioneMatrice($res, $campi);
            $responseData['elenco'] = $res;
            if ($tipoconc != 'GAD') {
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

    function _giochiDisponibiliPerConcessioneNew($tipoConc=null)
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

    function _giochiDisponibiliPerConcessioneMonitoraggioInc($tipoConc)
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
                                        'cfaamsIn'    => $cf_utente,
                                       );

        $res = $this->db->selProspetti($arrayBind);
        if ($this->db->getError() != "") {
            $responseData['messaggio'] = $this->db->getError();
        } else if ($res == null) {
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
                                        'cfaamsIn'    => $cf_utente,
                                       );

        $res = $this->db->selProspetti($arrayBind);
        if ($this->db->getError() != "") {
            $responseData['messaggio'] = $this->db->getError();
        } else if ($res == null) {
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
                                                .$anno_r? $anno_r : 'tutti'.' SEMESTRE '. $semestre_r? $semestre_r : 'tutti';
                $responseData['tpl_name']    = 'Rapporto Concessorio Antiriciclaggio - Monitoraggio';
        }
    }

    function getResult1det(Request $request, Response $response) 
    {
        $request_data = $request->getParsedBody();
        $dett_anno_r             = $request_data['dettLinkAnno'];
        $dett_semestre_r         = $request_data['dettLinkSem'];
        $tipodett         = $request_data['dettlink'];
        $cf         = $request_data['cf_utente'];
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
        } else if ($res == null) {
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
        $dett_anno_r             = $request_data['dettLinkAnno'];
        $dett_semestre_r         = $request_data['dettLinkSem'];
        $tipodett         = $request_data['dettlink'];
        $cf         = $request_data['cf_utente'];

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
        } else if ($res == null) {
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

    function getResultNew(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();
        $anno             = $request_data['anno'];
        $semestre         = $request_data['semestre'];
        $tipo_conc         = $request_data['tipoconc'];
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
        } else if ($res == null) {
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

    function getCSVNew(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();
        $anno             = $request_data['anno'];
        $semestre         = $request_data['semestre'];
        $tipo_conc         = $request_data['tipoconc'];
        $tipogioco        = $request_data['tipogioco']; 
        $tiporete         = $request_data['tiporete'] ;


        $responseData['anno']      = $anno;
        $responseData['semestre']  = $semestre;
        $responseData['tipoconc']  = $tipo_conc;
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
        } else if ($res == null) {
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



    //XLS_elenco_societa
    function antiric_XLS_elenco_societa(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();

        $format_titolo = array(
            'font_family' => 'Arial',
            'font_size'   => 10,
            'font_color'  => 'white',
            'font_weight' => 'B',
            'align'       => 'C',
            'fgcolor'     => 'red',
            'bgcolor'     => 'white',
            'border'      => 0,
            'num_format'  => 0,
        );

        $format_sotto_titolo = array(
            'font_family' => 'Arial',
            'font_size'   => 10,
            'font_color'  => 'white',
            'font_weight' => 'B',
            'align'       => 'C',
            'fgcolor'     => 'green',
            'bgcolor'     => 'white',
            'border'      => 0,
            'num_format'  => 0,
        );

        $format_intestazioni = array(
            'font_family' => 'Arial',
            'font_size'   => 10,
            'font_color'  => 'black',
            'font_weight' => 'B',
            'align'       => 'C',
            'fgcolor'     => 'silver',
            'bgcolor'     => 'white',
            'column_size' => 22,
        );

        $arrayBind       = array();
        $res= $this->db->ListaGiochi($arrayBind);
        $responseData['resGiochi'] = $res;
        if ($this->db->getError() != "") {
            $responseData['messaggio'] = 'Errore procedura ListaGiochi';
        } else if ($res == null) {
            $responseData['messaggio'] = 'Nessun record presente';
        } 

        $arrayBind       = array(
            'cod_fisc'    => $request_data['codFisc'],
            'annoSel'     => $request_data['annoRicerca'],
            'semestreSel' => $request_data['semestreRicerca'],
            'TipoOpe'     => '4',
            'CF_utente'   => '',
        );
        $resInvii = $this->db->ElencoInvii($arrayBind);
        $responseData['resInvii'] = $resInvii;
        if ($this->db->getError() != "") {
            $responseData['messaggio'] = 'Errore procedura ListaGiochi';
        } else if ($res == null) {
            $responseData['messaggio'] = 'Nessun record presente';
        } 



        $responseData['save_file']    = 0;
        $responseData['freeze_panes'] = 0;

        $responseData['nome_file_excel'] = 'Elenco_invii_documentazione.xls';

        $responseData['title_excel']['title']['testo']  =
        'Agenzia delle Dogane e dei Monopoli - Rapporto Concessorio - Antiriciclaggio - Monitoraggio';
        $responseData['title_excel']['title']['format'] = $format_titolo;

        $titoloTab                                          =
        'Invio documentazione per concessione e per gioco';
        $titoloTab                                         .=
        ' - Codice Fiscale: '.$resInvii['COD_FISC'][0];
        $titoloTab                                         .=
        ' - Ragione Sociale: '.$resInvii['RAG_SOC'][0];
        $titoloTab                                         .=
        ' - Anno: '.ucfirst($request_data['annoRicerca']);
        $titoloTab                                         .=
        ' - Semestre: '.ucfirst($request_data['semestreRicerca']);
        $responseData['title_excel']['newtitle']['testo']  = $titoloTab;
        $responseData['title_excel']['newtitle']['format'] = $format_sotto_titolo;

        $responseData['array_dati'][0] = $resInvii['TIPO_CONC'];
        $responseData['array_dati'][1] = $resInvii['TIPO_DIRITTO'];
        $responseData['array_dati'][2] = $resInvii['COD_CONC'];

        $colnna = 3;
        if($request_data['annoRicerca'] === 'tutti') {
            $responseData['array_dati'][$colnna] = $resInvii['ANNO'];
        $colnna++;
        }

        if($request_data['semestreRicerca'] === "tutti") {
            $responseData['array_dati'][$colnna] = $resInvii['SEMESTRE'];
        $colnna++;
        }

        $responseData['array_dati'][$colnna] = $resInvii['G0XLS'];

        $colnna++;
        $responseData['array_dati'][$colnna] = $resInvii['G1XLS'];

        $colnna++;
        $responseData['array_dati'][$colnna] =$resInvii['G10XLS'];

        $colnna++;
        $responseData['array_dati'][$colnna] = $resInvii['G11XLS'];

        $colnna++;
        $responseData['array_dati'][$colnna] = $resInvii['G12XLS'];

        $colnna++;
        $responseData['array_dati'][$colnna] = $resInvii['G2XLS'];

        $colnna++;
        $responseData['array_dati'][$colnna] = $resInvii['G5XLS'];

        $colnna++;
        $responseData['array_dati'][$colnna] = $resInvii['G6XLS'];

        $colnna++;
        $responseData['array_dati'][$colnna] = $resInvii['G7XLS'];

        $colnna++;
        $responseData['array_dati'][$colnna] = $resInvii['G8XLS'];


        $responseData['title_excel'][0]['testo']  = 'Tipo_concessione';
        $responseData['title_excel'][0]['format'] = $format_intestazioni;
        $responseData['title_excel'][1]['testo']  = 'Tipo_diritto';
        $responseData['title_excel'][1]['format'] = $format_intestazioni;
        $responseData['title_excel'][2]['testo']  = 'Codice_Concessione';
        $responseDatat['title_excel'][2]['format'] = $format_intestazioni;

        $colnna = 3;
        if($request_data['annoRicerca'] === 'tutti') {
            $responseData['title_excel'][$colnna]['testo']  = 'Anno';
            $responseData['title_excel'][$colnna]['format'] = $format_intestazioni;
        $colnna++;
        }

        if($request_data['semestreRicerca'] === 'tutti') {
            $responseData['title_excel'][$colnna]['testo']  = 'Semestre';
            $responseData['title_excel'][$colnna]['format'] = $format_intestazioni;
        $colnna++;
        }

        $responseData['title_excel'][$colnna]['testo']  = 'GIOCO_A_DISTANZA';
        $responseData['title_excel'][$colnna]['format'] = $format_intestazioni;

        $colnna++;
        $responseData['title_excel'][$colnna]['testo']  =
        'SCOMMESSE_IPPICHE_A_TOTALIZZATORE_E_A_QUOTA_FISSA';
        $responseData['title_excel'][$colnna]['format'] = $format_intestazioni;

        $colnna++;
        $responseData['title_excel'][$colnna]['testo']  = 'VIDEOGIOCHI';
        $responseData['title_excel'][$colnna]['format'] = $format_intestazioni;

        $colnna++;
        $responseData['title_excel'][$colnna]['testo']  = 'SCOMMESSE_SU_EVENTI_VIRTUALI';
        $responseData['title_excel'][$colnna]['format'] = $format_intestazioni;

        $colnna++;
        $responseData['title_excel'][$colnna]['testo']  = 'BETTING_EXCHANGE';
        $responseData['title_excel'][$colnna]['format'] = $format_intestazioni;

        $colnna++;
        $responseData['title_excel'][$colnna]['testo']  =
        'SCOMMESSE_A_TOTALIZZATORE_NON_IPPICHE';
        $responseData['title_excel'][$colnna]['format'] = $format_intestazioni;

        $colnna++;
        $responseData['title_excel'][$colnna]['testo']  = 'IPPICA_NAZIONALE';
        $responseData['title_excel'][$colnna]['format'] = $format_intestazioni;

        $colnna++;
        $responseData['title_excel'][$colnna]['testo']  = 'SCOMMESSE_A_QUOTA_FISSA_NON_IPPICHE';
        $responseData['title_excel'][$colnna]['format'] = $format_intestazioni;

        $colnna++;
        $responseData['title_excel'][$colnna]['testo']  = 'GIOCHI_DI_ABILITA\'';
        $responseData['title_excel'][$colnna]['format'] = $format_intestazioni;

        $colnna++;
        $responseData['title_excel'][$colnna]['testo']  = 'BINGO';
        $responseData['title_excel'][$colnna]['format'] = $format_intestazioni;

        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il reperimento del dettaglio"];
            return $response->withJson($data, 500);
        }
    }
    //FINE -- XLS_elenco_societa


    //XLS_elenco_operazioni
    function antiric_XLS_elenco_operazioni(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();

 
        $format_titolo = array(
            'font_family' => 'Arial',
            'font_size'   => 10,
            'font_color'  => 'white',
            'font_weight' => 'B',
            'align'       => 'C',
            'fgcolor'     => 'red',
            'bgcolor'     => 'white',
            'border'      => 0,
            'num_format'  => 0,
        );

        $format_sotto_titolo = array(
                'font_family' => 'Arial',
                'font_size'   => 10,
                'font_color'  => 'white',
                'font_weight' => 'B',
                'align'	      => 'C',
                'fgcolor'     => 'green',
                'bgcolor'     => 'white',
                'border'      => 0,
                'num_format'  => 0,
                );

        $format_intestazioni = array(
                'font_family' => 'Arial',
                'font_size'   => 10,
                'font_color'  => 'black',
                'font_weight' => 'B',
                'align'       => 'C',
                'fgcolor'     => 'silver',
                'bgcolor'     => 'white',
                'column_size' => 42,
                );

        $arrayBind = array(
        'tipo_concIn' => $request_data['tipo_conc'],
        'cod_concIn'  => $request_data['cod_conc'],
        'annoIn'      => $request_data['annoRicerca'],
        'semestreIn'  => $request_data['semestreRicerca'],
        'cod_giocoIn' => $request_data['cod_gioco'],
        'tipo_reteIn' => $request_data['tipo_rete'],
        );

        $resQuery = $this->db->selezioneDatiTrasmessiCSV($arrayBind);
        $responseData['resQuery'] = $resQuery;
        if ($this->db->getError() != "") {
            $responseData['messaggio'] = 'Errore procedura ListaGiochi';
        } else if ($resQuery == null) {
            $responseData['messaggio'] = 'Nessun record presente';
        } 


        $responseData['save_file']       = 0;
        $responseData['freeze_panes']    = 0;
        $responseData['nome_file_excel'] = 'Elenco_operazioni.xls';

        $responseData['title_excel']['title']['testo']  = 'Agenzia delle Dogane e dei Monopoli - Rapporto Concessorio - Antiriciclaggio - Monitoraggio';
        $responseData['title_excel']['title']['format'] = $format_titolo;

        $titoloTab  = 'Invio documentazione per concessione e per gioco';
        $titoloTab .= ' - Codice Fiscale: '.$request_data['codFisc'];
        $titoloTab .= ' - Ragione Sociale: '.$request_data['ragSoc'];
        $titoloTab .= ' - Anno: '.$request_data['annoRicerca'];
        $semestre   = 'Primo';
        if($request_data['semestreRicerca'] === 2) {
            $semestre = 'Secondo';
        }

        $titoloTab                                         .= ' - Semestre: '.$semestre;
        $responseData['title_excel']['newtitle']['testo']  = $titoloTab;
        $responseData['title_excel']['newtitle']['format'] = $format_sotto_titolo;

        $arrayTitle = array(
        0  => 'CODICE CONCESSIONE',
        1  => 'TIPO CONCESSIONE',
        2  => 'GIOCO',
        3  => 'Tipo raccolta',
        4  => 'CODICE FORNITURA',
        5  => 'DATA TRASMISSIONE',
        6  => 'GIOCATE SUPERIORI A 1000 EURO',
        7  => 'VINCITE SUPERIORI A 1000 EURO',
        8  => 'OPERAZIONI FRAZIONATE',
        9  => 'OPERAZIONI SOSPETTE',
        10 => 'GIOCATE DWH',
        11 => 'VINCITE DWH',
        12 => 'OPERAZIONI FRAZIONATE DWH',
        13 => 'OPERAZIONI SOSPETTE DWH',
        );

        for($i = 0; $i < 14; $i++) 
        {
            if(isset($resQuery[$arrayTitle[$i]][0])) {
                $valorecampo = strtolower($resQuery[$arrayTitle[$i]][0]);
        
                switch ($valorecampo) {
                    case 'nd':
                        $valorecampo = 'Non disponibile';
                    break;
        
                    case 'ne':
                        $valorecampo = 'Non esiste';
                    break;
        
                    case 'dwh':
                        $valorecampo = 'Data WareHouse';
                    break;
                }
                
                $arrayValue[$i] = ucfirst($valorecampo);
            }
            else {
                $arrayValue[$i] = 'Non disponibile';
            }
        }


        $responseData['array_dati'][0] = $arrayTitle;
        $responseData['array_dati'][1] = $arrayValue;

        $responseData['title_excel'][0]['testo']  = 'Operazioni';
        $responseData['title_excel'][0]['format'] = $format_intestazioni;

        $responseData['title_excel'][1]['testo']  = 'Valori';
        $responseData['title_excel'][1]['format'] = $format_intestazioni;

        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il reperimento del dettaglio"];
            return $response->withJson($data, 500);
        }
    }
    //FINE -- XLS_elenco_operazioni


    //INIZIO -- antiric_deroga.inc
    function getFormDeroga(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();
        $concessionario = null;
        $anno           = null;
        $semestre       = null;
        $datadal        = null;
        $dataal         = null;
        
        if (isset($request_data['option_giorno_dal']) and isset($request_data['option_mese_dal']) and isset($request_data['option_anno_dal'])) 
            $datadal = str_pad($request_data['option_giorno_dal'],2,'0',STR_PAD_LEFT).str_pad($request_data['option_mese_dal'],2,'0',STR_PAD_LEFT)
	        .str_pad($request_data['option_anno_dal'],2,'0',STR_PAD_LEFT);
	    if (isset($request_data['option_giorno_al']) and isset($request_data['option_mese_al']) and isset($request_data['option_anno_al']))     
	        $dataal  = str_pad($request_data['option_giorno_al'],2,'0',STR_PAD_LEFT).str_pad($request_data['option_mese_al'],2,'0',STR_PAD_LEFT).
	        str_pad($request_data['option_anno_al'],2,'0',STR_PAD_LEFT);
        
        if (isset($request_data['concessionario'])) {
            $concessionario = $request_data['concessionario'];
        }
        if (isset($request_data['anno'])) {
            $anno = $request_data['anno'];
        }
        if (isset($request_data['semestre'])) {
            $semestre = $request_data['semestre'];
        }
        $responseData['concessionario'] = $concessionario;
        $responseData['semestre']       = $semestre;
        $responseData['anno']           = $anno;
        $responseData['flag_autorizza']  = 0; //vedi tasto autorizza
        
        $responseData['pars']           = $this->clear_pars;
        
        if (isset($request_data['tipoconc'])) {
            $tipo = $request_data['tipoconc'];
        } else {
            $tipo = 'T';
        }
        
        $responseData['tipoconc']         = $tipo;
                        
        if (isset($request_data['autorizza']) && $request_data['autorizza'] == 'Autorizza') 
         {
	      //echo "\nautorizza";
	      $rit = $this->_checkForm($request_data);
	      //echo "\nrit=".$rit;
	      if($rit == '0')
	      {  
                $arrayBind = array('tipo_conc'  => $tipo,
                                'cod_conc'   => $concessionario,
                                'anno'       => $anno,
                                'semestre'   => $semestre,
                                'datadal'    => $datadal,
                                'dataal'     => $dataal,
                                'cfaams_in' => $this->clear_pars['cf_utente']);
                $resQuery = $this->db->ScriviDeroga($arrayBind);
                $responseData['resQuery'] = $resQuery;
                if ($this->db->getError() != "") {
                    $responseData['messaggio'] = 'Operazione terminata con errore - Contattare assistenza';
                } else 
                {
                    $responseData['messaggio'] = 'Deroga correttamente autorizzata ';
                }                  

                
                $responseData['flag_autorizza']  = 1; //nascondi tasto autorizza  
          }  
          else
            $this->tpl_dat['messaggio']=$rit;  
            
            
	      //caricamento elenco concessioni
          $arrayBind = array('tipo_conc' => $tipo);
          $res = $this->db->selConcDeroga($arrayBind);
          $responseData['resQuery'] = $res;
          if ($this->db->getError() != "") {
              $responseData['messaggio'] = 'Errore procedura Deroga';
          } else if ($res == null) {
            $responseData['messaggioConcessionari']='Non sono presenti concessionari per i parametri di ';
            $responseData['messaggioConcessionari'] .= 'ricerca selezionati';
          } 

	     
	      $concOpt = array();
	      if ($this->db->errcode == 0 && $res != null) {
	          foreach ($res['COD_CONC'] as $k => $v) {
	              if ($res['TIPO_CONC'][$k] == 'VID') {
	                    $concOpt[$res['TIPO_CONC'][$k].$v] =
	                    (isset($res['RAG_SOC'][$k])? $res['RAG_SOC'][$k] : '');
	                } else {
	                 $concOpt[$v] = $v.' - '.(isset($res['RAG_SOC'][$k])?$res['RAG_SOC'][$k] : '');
	                }
	            }
	       }
	
           $responseData['tipoconcSelected'] = $tipo;
           $responseData['concessionari']    = $concOpt;
	      
	      
          
         }
         
        if (isset($request_data['visualizza']) && $request_data['visualizza'] == 'Visualizza>>') 
         {  
	        if (isset($request_data['tipoconc']) && $request_data['tipoconc'] == '') 
	         {
                $responseData['messaggio']='Selezionare tipologia di concessione';
             }
	        else
	         { 
	           //caricamento elenco concessioni
                $arrayBind = array('tipo_conc' => $tipo);
                $res = $this->db->selConcDeroga($arrayBind);
                $responseData['resQuery'] = $res;
                if ($this->db->getError() != "") {
                    $responseData['messaggio'] = 'Errore procedura Deroga';
                } else if ($res == null) {
                  $responseData['messaggioConcessionari']='Non sono presenti concessionari per i parametri di ';
                  $responseData['messaggioConcessionari'] .= 'ricerca selezionati';
                } 

         
	            $concOpt = array();
	            if ($this->db->errcode == 0 && $res != null) {
	                foreach ($res['COD_CONC'] as $k => $v) {
	                    if ($res['TIPO_CONC'][$k] == 'VID') {
	                        $concOpt[$res['TIPO_CONC'][$k].$v] =
	                        (isset($res['RAG_SOC'][$k])? $res['RAG_SOC'][$k] : '');
	                    } else {
	                     $concOpt[$v] = $v.' - '.(isset($res['RAG_SOC'][$k])?$res['RAG_SOC'][$k] : '');
	                    }
	                }
	            }
	
	            $responseData['tipoconcSelected'] = $tipo;
	            $responseData['concessionari']    = $concOpt;
             }    
         }

        //caricamento tipologia di concessione
        $arrayBind   = array('allIn' => '1',);
        $res = $this->db->selTipoConc($arrayBind);
        $tipoConcOpt = array();
        foreach ($res['TIPO_CONC'] as $k => $v) {
            $tipoConcOpt[$v] = $res['DESCRIZIONE'][$k];
        }

        $responseData['tipoconcessioni'] = $tipoConcOpt;
        if ($this->db->errcode == 0) {
            if (isset($concOpt)) {
                $responseData['select_concessionari'] = $concOpt;
            }
        } else {
            $responseData['messaggio'] = $this->db->err;
        }
        //caricamento giorno data inizio
        $giorno_dal    = array();
        $giorno_dal[0] = '';
        for($gg=1;$gg<=31;$gg++) {
          $giorno_dal[$gg] = str_pad($gg, 2, "0", STR_PAD_LEFT);
        }
        $responseData['giorno_dal'] = $giorno_dal;
        //caricamento mese data inizio
        $mese_dal    = array();
        $mese_dal[0] = '';
        for($mm=1;$mm<=12;$mm++){
          $mese_dal[$mm] = str_pad($mm, 2, "0", STR_PAD_LEFT);
        }
        $responseData['mese_dal'] = $mese_dal;
        //caricamento anno data inizio
        $anno_dal    = array();
        $anno_dal[0] = '';
        for($aa=date("Y");$aa<=date("Y")+2;$aa++) {
          $anno_dal[$aa] = $aa;
        }
        $responseData['anno_dal'] = $anno_dal;
        //caricamento giorno data fine
        $giorno_al    = array();
        $giorno_al[0] = '';
        for($gg=1;$gg<=31;$gg++) {
          $giorno_al[$gg] = str_pad($gg, 2, "0", STR_PAD_LEFT);
        }
        $responseData['giorno_al'] = $giorno_al;
        //caricamento mese data fine
        $mese_al    = array();
        $mese_al[0] = '';
        for($mm=1;$mm<=12;$mm++){
          $mese_al[$mm] = str_pad($mm, 2, "0", STR_PAD_LEFT);
        }
        $responseData['mese_al'] = $mese_al;
        //caricamento anno data fine
        $anno_al    = array();
        $anno_al[0] = '';
        for($aa=date("Y");$aa<=date("Y")+2;$aa++) {
          $anno_al[$aa] = $aa;
        }
        $responseData['anno_al'] = $anno_al;
        if (isset($request_data['option_giorno_dal'])) 
            $responseData['giorno_dal_sel'] = $request_data['option_giorno_dal'];
        if (isset($request_data['option_mese_dal']))   
            $responseData['mese_dal_sel'] = $request_data['option_mese_dal'];
        if (isset($request_data['option_anno_dal']))   
            $responseData['anno_dal_sel'] = $request_data['option_anno_dal'];
        if (isset($request_data['option_giorno_al']))   
            $responseData['giorno_al_sel'] = $request_data['option_giorno_al'];
        if (isset($request_data['option_mese_al']))   
            $responseData['mese_al_sel'] = $request_data['option_mese_al'];
        if (isset($request_data['option_anno_al']))   
            $responseData['anno_al_sel'] = $request_data['option_anno_al'];

        
        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero dei dati trasmessi"];
            return $response->withJson($data, 500);
        }   
    }

    function getFormMon(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();
        $concessionario = null;
        $anno           = null;
        $semestre           = null;
       
        
        if (isset($request_data['concessionario'])) {
            $concessionario = $request_data['concessionario'];
        }
        if (isset($request_data['anno'])) {
            $anno = $request_data['anno'];
        }
        if (isset($request_data['semestre'])) {
            $semestre = $request_data['semestre'];
        } 
        $responseData['concessionario'] = $concessionario;
        $responseData['tiporaccolta']   = $request_data['traccolta'];
        $responseData['semestre']       = $semestre;
        $responseData['anno']           = $anno;
        $responseData['pars']           = $this->clear_pars;
        if (isset($request_data['tipoconc'])) {
            $tipo = $request_data['tipoconc'];
        } else {
            $tipo = 'T';
        }

        $responseData['tipoconc']         = $tipo;
    
        $arrayBind = array('tipo_conc'    => $tipo);
        $res = $this->db->selConcDeroga($arrayBind);
        $responseData['resQuery'] = $res;
        if ($this->db->getError() != "") {
            $responseData['messaggio'] = 'Errore procedura Deroga';
        } else if ($res == null) {
            $responseData['messaggioConcessionari']='Non sono presenti concessionari per i parametri di ';
            $responseData['messaggioConcessionari'] .= 'ricerca selezionati';
        } 


        $concOpt = array();
        if ($this->db->errcode == 0 && $res != null) {
            foreach ($res['COD_CONC'] as $k => $v) {
                if ($res['TIPO_CONC'][$k] == 'VID') {
                    $concOpt[$res['TIPO_CONC'][$k].$v] =
                    (isset($res['RAG_SOC'][$k])? $res['RAG_SOC'][$k] : '');
                } else {

                    $concOpt[$v] = $v.' - '.(isset($res['RAG_SOC'][$k])?$res['RAG_SOC'][$k] : '');
                }
            }
        }

        $responseData['tipoconcSelected'] = $tipo;
        $responseData['concessionari']    = $concOpt;
        //}

        $arrayBind   = array('allIn' => '1',);
        $res = $this->db->selTipoConc($arrayBind);
        $tipoConcOpt = array();
        foreach ($res['TIPO_CONC'] as $k => $v) {
            $tipoConcOpt[$v] = $res['DESCRIZIONE'][$k];
        }

        $responseData['tipoconcessioni'] = $tipoConcOpt;
        if ($this->db->errcode == 0) {
            if (isset($concOpt)) {
                $responseData['select_concessionari'] = $concOpt;
            }
        } else {
            $responseData['messaggio'] = $this->db->err;
        }

        
        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero dei dati trasmessi"];
            return $response->withJson($data, 500);
        }
        
    }

    function _checkForm($request_data)
    {
	 //echo "\ncheckform";
	 //echo "\ndata_odierna=".date("Ymd"); 
	 //echo "\ndata_inizio=".$this->clear_pars['option_anno_dal'].str_pad($this->clear_pars['option_mese_dal'],2,'0',STR_PAD_LEFT).str_pad($this->clear_pars['option_giorno_dal'],2,'0',STR_PAD_LEFT); 
	 //echo "\ndata_fine=".$this->clear_pars['option_anno_al'].str_pad($this->clear_pars['option_mese_al'],2,'0',STR_PAD_LEFT).str_pad($this->clear_pars['option_giorno_al'],2,'0',STR_PAD_LEFT);
	 //print_r($this->clear_pars);  
	 if ($request_data['option_mese_dal'] == 0 or $request_data['option_giorno_dal'] == 0 or $request_data['option_anno_dal']==0) 
	 {
	  return 'Data inizio trasmissione obbligatoria';  
     } 
	 elseif (!checkdate($request_data['option_mese_dal'], $request_data['option_giorno_dal'],$request_data['option_anno_dal']) )
	 {
	  return 'Data inizio trasmissione errata'; 
     } 
	 elseif ($request_data['option_mese_al'] == 0 or $request_data['option_giorno_al'] == 0 or $request_data['option_anno_al']==0) 
	 {
	  return 'Data fine trasmissione obbligatoria';  
     } 
	 elseif (!checkdate($request_data['option_mese_al'], $request_data['option_giorno_al'],$request_data['option_anno_al']) )
	 {
	  return 'Data fine trasmissione errata';   
     } 
	 elseif(date("Ymd") > $request_data['option_anno_dal'].str_pad($request_data['option_mese_dal'],2,'0',STR_PAD_LEFT).str_pad($request_data['option_giorno_dal'],2,'0',STR_PAD_LEFT) )
	 {
	   return 'Data inizio trasmissione non deve essere minore della data odierna';   
     } 
     elseif($request_data['option_anno_dal'].str_pad($request_data['option_mese_dal'],2,'0',STR_PAD_LEFT).str_pad($request_data['option_giorno_dal'],2,'0',STR_PAD_LEFT)
      > $request_data['option_anno_al'].str_pad($request_data['option_mese_al'],2,'0',STR_PAD_LEFT).str_pad($request_data['option_giorno_al'],2,'0',STR_PAD_LEFT))
	 {
	  return 'Data inizio trasmissione non deve essere maggiore della data fine trasmissione';   
     }
     elseif($request_data['concessionario'] == '')
	 {
	  return 'Concessione obbligatoria';   
     }
	 else 
	  return '0';
    } 


    function putFormMon(Request $request, Response $response)
    {
        $request_data = $request->getParsedBody();
        $concessionario = null;
        $anno           = null;
        $semestre       = null;
                
        if (isset($request_data['concessionario'])) {
            $concessionario = $request_data['concessionario'];
        }
        if (isset($request_data['anno'])) {
            $anno = $request_data['anno'];
        }
        if (isset($request_data['semestre'])) {
            $semestre = $request_data['semestre'];
        } else {
            $semestre = '-1';
        }
        if (isset($request_data['tipoconc'])) {
            $tipo = $request_data['tipoconc'];
        } else {
            $tipo = 'T';
        }
        
        
        if ($anno == 'T')
             $responseData['anno']           = 'Tutti';
        else 
        $responseData['anno']           = $anno;
        if ($semestre == 'T')
        $responseData['semestre']       = 'Tutti';
        else 
        $responseData['semestre']       = $semestre; 
        if ($concessionario == 'T')
        $responseData['concessionario'] = 'Tutti';
        else 
        $responseData['concessionario'] = $concessionario;  
        if ($tipo == 'T')
        $responseData['tipoconc'] = 'Tutti';
        else 
        $responseData['tipoconc'] = $this->_calcolaDescTipoConc($tipo);   
         
        $responseData['pars']           = $this->clear_pars;
        
        //echo "\ntipo=".$tipo;
        //echo "\nconcessionario=".$concessionario;
                
        if (isset($request_data['cerca']) && $request_data['cerca'] == 'Cerca') 
         {
                //echo "\ncerca";
                $arrayBind = array('tipo_conc'  => $tipo,
                                    'cod_conc'   => $concessionario,
                                    'anno'       => $anno,
                                    'semestre'   => $semestre);
                $res = $this->db->CercaDeroga($arrayBind);
                $responseData['resQuery'] = $res;

                if ($res == null) 
                {
                    $responseData['messaggio']='Non sono presenti deroghe per i parametri di ';
                    $responseData['messaggio'] .= 'ricerca selezionati';
                    $responseData['return']= 0;
                }

                $responseData['fine'] = count($res['COD_CONC']);     
                $responseData['elenco'] = $res; 
                //print_r($this->tpl_dat['dati']['ANNO']);
                 
         }    
        $responseData['return']=1;

        $response->write(json_encode($responseData));
        if (is_array($responseData)) {
            $data = ["status" => "OK", "result" => $responseData];
            return $response->withJson($data, 200);
        } else {
            $data = ["status" => "NOK", "message" => "Errore durante il recupero dei dati trasmessi"];
            return $response->withJson($data, 500);
        } 
    }

    function _calcolaDescTipoConc($tipoconc)
    {
        switch ($tipoconc) {
            case 'GAD':
                return 'GAD';
            break;

            case 'I':
                return 'GIOCHI PUBBLICI IPPICA';
            break;

            case 'VID':
                return 'VIDEOGIOCHI';
            break;

            case 'AI':
                return 'AGENZIA I';
            break;

            case 'AS':
                return 'AGENZIA SPORTIVA';
            break;

            case 'S':
                return 'GIOCHI PUBBLICI SPORT';
            break;

            case 'B':
                return 'BINGO';
            break;

           case 'IDLG':
                return 'GIOCHI PUBBLICI IPPICA D.L. 149/08';
            break;

            default:
                // Azione non prevista.
            break;
        }
    }


}

