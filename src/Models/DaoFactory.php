<?php

namespace NIM_Backend\Models;

use Exception as Exception;
use NIM_Backend\SharedLibs\DBManager;

class DaoFactory
{

    protected $logger;
    protected $package;
    protected $nomignolo;
    protected $msgError;
    private   $mock = true;

    public function __construct(\Monolog\Logger $logger, $dbMan)
    {
        global $appConfig;

        $this->config = $appConfig;
        $this->logger = $logger;
        $this->package = "RC01_antiriclaggio"; //$package;
        $this->nomignolo = "GP"; //"RC";
        $this->msgError = "";

        $this->db = $dbMan;
    }

    protected function commonCall($nomeProcedura = null, $parIn = null, $parOut = null, $blobIn = null, $blobOut = null, $cursor = null)
    {
        $rst = null;
        $this->msgError = "";
        $this->logger->debug("DB STORE_PROCEDURE:  $nomeProcedura");
        try {
            $this->db->initConnection($this->nomignolo);
            if (isset($parIn))   $this->db->setParametriIn($parIn);
            if (isset($parOut))  $this->db->setParametriOut($parOut);
            if (isset($blobIn))  $this->db->setBlobIn($blobIn);
            if (isset($blobOut)) $this->db->setBlobOut($blobOut);
            if (isset($cursor))  $this->db->setCursor($cursor);
            $this->db->setProcedura($nomeProcedura);
            $rst = $this->db->esegui();
        } catch (Exception $e) {
            $this->logger->error("Exception: " . $nomeProcedura);
            $this->logger->error("parIn",  [$parIn]);
            $this->logger->error("parOut", [$parOut]);
            $this->logger->error("Except", [$e]);
            $this->msgError = "Exception: " . $nomeProcedura . PHP_EOL . "parIn : " . [$parIn] . PHP_EOL . "parOut : " . [$parOut] . PHP_EOL . "Except : " . [$e];
            return null;
        }

        if ($rst['ERRCODE'] == 2) {
            $this->msgError = $rst['ERRTEXT'];
            $this->logger->error($rst['ERRTEXT']);
        }
        if ($rst['ERRCODE'] == 1) {
            $this->msgError = 'Si &egrave; verificato un errore, contattare l&rsquo;amministratore del sistema.';
            $this->logger->error($this->msgError);
        }
        if ($this->config['debug'] === true) {
            $this->logger->debug("Called: " . $nomeProcedura);
            $this->logger->debug("parIn",  [$parIn]);
            $this->logger->debug("parOut", [$parOut]);
            $this->logger->debug("Result", [$rst]);
        }
        $this->db->closeConnection();
        return $rst;
    }

    public function getError()
    {
        return $this->msgError;
    }

    public function selezioneConcessionari($arrayParams)
    {
        if (!$this->mock) {
            $retVal = $this->commonCall(
                $this->package . ".selConcNew",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        } else {
            $retVal = ['res' => [
                [
                    "RAG_SOC" => "COGETECH GAMING S.R.L. - SOCIO UNICO",
                    "COD_CONC" => "1",
                    "DESCR" => "AGENZIA SPORTIVA",
                    "TIPO_CONC" => "AS",
                    "ID_CONC" => "1"
                ],
                [
                    "RAG_SOC" => "BINGO NEMICO SRL",
                    "COD_CONC" => "1",
                    "DESCR" => "BINGO",
                    "TIPO_CONC" => "B",
                    "ID_CONC" => "2"
                ],
                [
                    "RAG_SOC" => null,
                    "COD_CONC" => "1",
                    "DESCR" => "APPARECCHI DA INTRATTENIMENTO",
                    "TIPO_CONC" => "VID",
                    "ID_CONC" => "2"
                ],
                [
                    "RAG_SOC" => "CITES SPA",
                    "COD_CONC" => "2",
                    "DESCR" => "BINGO",
                    "TIPO_CONC" => "B",
                    "ID_CONC" => "2"
                ]

            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }

        if ($retVal == null) {
            $this->msgError = 'Non sono presenti concessionari per i parametri di ';
            $this->msgError .= 'ricerca selezionati';
        }


        return (isset($retVal)) ? $retVal['res'] : null;
    }

    public function selezioneTipoConcessionari($arrayParams)
    {
        if (!$this->mock) {
            $retVal = $this->commonCall(
                $this->package . ".selTipoConc",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        } else {
            $retVal = ['res' => [
                [
                    "TIPO_CONC" => "A",
                    "DESCRIZIONE" => "TITOLARE RACCOLTA SCOMMESSE"
                ],
                [
                    "TIPO_CONC" => "AI",
                    "DESCRIZIONE" => "AGENZIA IPPICA"
                ],

            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }


        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selezioneDatiTrasmessi($arrayParams)
    {
        if (!$this->mock) {
            $retVal = $this->commonCall(
                $this->package . ".selDatiTrasmessi",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        } else {
            $retVal = ['res' => [
                [
                    "TIPO_CONC"                 => "A",
                    "RAG_SOC"                 => "RAGIONE SOCIALE 1",
                    "COD_CONC"                 => "1",
                    "DESCRIZIONE"             => "CONCESSIONARIO 1",
                    "ANNO"                     => "2015",
                    "SEMESTRE"                 => "1",
                    "GIOCATE_DWH"             => "1",
                    "VINCITE_DWH"             => "2",
                    "FLAG_DWH"                 => "1",
                    "OPERAZ_FRAZ_DWH"         => "1",
                    "RICARICHE_1000_DWH"     => "2",
                    "RICARICHE_15000_DWH"     => "3",
                    "PRELIEVI_1000_DWH"         => "4",
                    "PRELIEVI_15000_DWH"     => "5",
                    "CARTE_1000_DWH"         => "6",
                    "CARTE_15000_DWH"         => "7",
                    "CARTE_TORNEO_15000_DWH" => "8",
                    "FLAG_DWH"                 => "1",
                    "GIOCATE"                 => "2",
                    "VINCITE"                 => "3",
                    "OPERAZ_FRAZ"             => "4",
                    "RICARICHE_1000"         => "5",
                    "RICARICHE_15000"         => "6",
                    "PRELIEVI_1000"             => "7",
                    "PRELIEVI_15000"         => "8",
                    "CARTE_1000"             => "9",
                    "CARTE_15000"             => "12",
                    "CARTE_TORNEO_15000"     => "11",
                    "OPERAZ_SOSP"             => "1",
                    "COD_GIOCO"                 => "1",
                    "DESCR_GIOCO"             => "GIOCO 1",
                    "TIPO_RETE"                 => "TIPO RETE 1",
                    "DATA_CONC"                => "01/01/2015"
                ],
                [
                    "TIPO_CONC"                 => "L",
                    "RAG_SOC"                 => "RAGIONE SOCIALE 2",
                    "COD_CONC"                 => "2",
                    "DESCRIZIONE"             => "CONCESSIONARIO 2",
                    "ANNO"                     => "2016",
                    "SEMESTRE"                 => "2",
                    "GIOCATE_DWH"             => "4",
                    "VINCITE_DWH"             => "5",
                    "FLAG_DWH"                 => "0",
                    "OPERAZ_FRAZ_DWH"         => "2",
                    "RICARICHE_1000_DWH"     => "4",
                    "RICARICHE_15000_DWH"     => "1",
                    "PRELIEVI_1000_DWH"         => "3",
                    "PRELIEVI_15000_DWH"     => "4",
                    "CARTE_1000_DWH"         => "5",
                    "CARTE_15000_DWH"         => "6",
                    "CARTE_TORNEO_15000_DWH" => "7",
                    "FLAG_DWH"                 => "0",
                    "GIOCATE"                 => "2",
                    "VINCITE"                 => "3",
                    "OPERAZ_FRAZ"             => "4",
                    "RICARICHE_1000"         => "5",
                    "RICARICHE_15000"         => "6",
                    "PRELIEVI_1000"             => "7",
                    "PRELIEVI_15000"         => "8",
                    "CARTE_1000"             => "9",
                    "CARTE_15000"             => "12",
                    "CARTE_TORNEO_15000"     => "11",
                    "OPERAZ_SOSP"             => "1",
                    "COD_GIOCO"                 => "1",
                    "DESCR_GIOCO"             => "GIOCO 2",
                    "TIPO_RETE"                 => "TIPO RETE 2",
                    "DATA_CONC"                => "01/01/2016"
                ]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }

        if ($retVal == null) {
            $this->msgError = 'Non sono presenti dati trasmessi per i parametri di ';
            $this->msgError .= 'ricerca selezionati';
        }

        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selezioneDatiTrasmessiCSV($arrayParams)
    {
        if (!$this->mock) {
            $retVal = $this->commonCall(
                $this->package . ".selDatiTrasmessiCSV",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        } else {
            $retVal = ['res' => [
                [
                    "TIPO CONCESSIONE"                    => "1",
                    "CODICE CONCESSIONE"                => "1",
                    "ANNO"                                => "2015",
                    "SEMESTRE"                            => "1",
                    "Q1"                                => "0",
                    "Q2"                                => "0",
                    "Q3"                                => "0",
                    "Q4"                                => "0",
                    "Q5"                                => "0",
                    "Q6"                                => "0",
                    "Q7"                                => "0",
                    "Q8"                                => "0",
                    "Q1 DWH"                            => "0",
                    "Q2 DWH"                            => "0",
                    "Q3 DWH"                            => "0",
                    "Q4 DWH"                            => "0",
                    "Q5 DWH"                            => "0",
                    "Q6 DWH"                            => "0",
                    "Q7 DWH"                            => "0",
                    "Q8 DWH"                            => "0",
                    "GIOCATE SUPERIORI A 1000 EURO"        => "0",
                    "VINCITE SUPERIORI A 1000 EURO"        => "0",
                    "OPERAZIONI FRAZIONATE"                => "0",
                    "OPERAZIONI SOSPETTE"                => "0",
                    "GIOCATE DWH"                        => "0",
                    "VINCITE DWH"                        => "0",
                    "OPERAZIONI FRAZIONATE DWH"            => "0",
                    "OPERAZIONI SOSPETTE DWH"            => "0",
                    "Ricariche 1000"                    => "0",
                    "Ricariche 15000"                    => "0",
                    "PRELIEVI 1000"                        => "0",
                    "PRELIEVI 15000"                    => "0",
                    "CARTE 1000"                        => "0",
                    "CARTE 15000"                        => "0",
                    "CARTE TORNEO 15000"                => "0",
                    "RICARICHE 1000 DWH"                => "0",
                    "RICARICHE 15000 DWH"                => "0",
                    "PRELIEVI 1000 DWH"                    => "0",
                    "PRELIEVI 15000 DWH"                => "0",
                    "CARTE 1000 DWH"                    => "0",
                    "CARTE 15000 DWH"                    => "0",
                    "CARTE TORNEO 15000 DWH"            => "0",
                    "GIOCO"                                => "Gioco 1",
                    "Tipo raccolta"                        => "Tipo Raccolta 1",
                    "CODICE FORNITURA"                    => "1",
                    "DATA TRASMISSIONE"                 => "01/01/2015"
                ],
                [
                    "TIPO CONCESSIONE"                    => "2",
                    "CODICE CONCESSIONE"                => "2",
                    "ANNO"                                => "2016",
                    "SEMESTRE"                            => "1",
                    "Q1"                                => "0",
                    "Q2"                                => "0",
                    "Q3"                                => "0",
                    "Q4"                                => "0",
                    "Q5"                                => "0",
                    "Q6"                                => "0",
                    "Q7"                                => "0",
                    "Q8"                                => "0",
                    "Q1 DWH"                            => "0",
                    "Q2 DWH"                            => "0",
                    "Q3 DWH"                            => "0",
                    "Q4 DWH"                            => "0",
                    "Q5 DWH"                            => "0",
                    "Q6 DWH"                            => "0",
                    "Q7 DWH"                            => "0",
                    "Q8 DWH"                            => "0",
                    "GIOCATE SUPERIORI A 1000 EURO"        => "0",
                    "VINCITE SUPERIORI A 1000 EURO"        => "0",
                    "OPERAZIONI FRAZIONATE"                => "0",
                    "OPERAZIONI SOSPETTE"                => "0",
                    "GIOCATE DWH"                        => "0",
                    "VINCITE DWH"                        => "0",
                    "OPERAZIONI FRAZIONATE DWH"            => "0",
                    "OPERAZIONI SOSPETTE DWH"            => "0",
                    "Ricariche 1000"                    => "0",
                    "Ricariche 15000"                    => "0",
                    "PRELIEVI 1000"                        => "0",
                    "PRELIEVI 15000"                    => "0",
                    "CARTE 1000"                        => "0",
                    "CARTE 15000"                        => "0",
                    "CARTE TORNEO 15000"                => "0",
                    "RICARICHE 1000 DWH"                => "0",
                    "RICARICHE 15000 DWH"                => "0",
                    "PRELIEVI 1000 DWH"                    => "0",
                    "PRELIEVI 15000 DWH"                => "0",
                    "CARTE 1000 DWH"                    => "0",
                    "CARTE 15000 DWH"                    => "0",
                    "CARTE TORNEO 15000 DWH"            => "0",
                    "GIOCO"                                => "Gioco 2",
                    "Tipo raccolta"                        => "Tipo Raccolta 2",
                    "CODICE FORNITURA"                    => "2",
                    "DATA TRASMISSIONE"                 => "01/01/2016"
                ]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selezioneGiochi($arrayParams)
    {
        if (!$this->mock) {
            $retVal = $this->commonCall(
                $this->package . ".selGiochi",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        } else {
            $retVal = ['res' => [
                ["COD_GIOCO" => "1", "DESCR_GIOCO" => "Gioco 1"], ["COD_GIOCO" => "2", "DESCR_GIOCO" => "Gioco 2"], ["COD_GIOCO" => "3", "DESCR_GIOCO" => "Gioco 3"]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selElencoInadempienti($arrayParams)
    {
        if (!$this->mock) {
            $retVal = $this->commonCall(
                $this->package . ".selElencoInadempienti",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        } else {
            $retVal = ['res' => [], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selProspetti($arrayParams)
    {
        if (!$this->mock) {
            $retVal = $this->commonCall(
                $this->package . ".selProspetti",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        } else {
            $retVal = ['res' => [], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function calcolaStatDett($arrayParams)
    {
        if (!$this->mock) {
            $retVal = $this->commonCall(
                $this->package . ".calcolaStatDett",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        } else {
            $retVal = ['res' => [], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selElencoInadempientiNew($arrayParams)
    {
        if (!$this->mock) {
            $retVal = $this->commonCall(
                $this->package . ".selElencoInadempientiNew",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        } else {
            $retVal = ['res' => [], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function ListaGiochi($arrayParams)
    {
        if (!$this->mock) {
            $retVal = $this->commonCall(
                $this->package . ".ListaGiochi",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        } else {
            $retVal = ['res' => [], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function ElencoInvii($arrayParams)
    {

        if (!$this->mock) {
            $retVal = $this->commonCall(
                $this->package . ".ElencoInvii",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        } else {
            $retVal = ['res' => [], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function ScriviDeroga($arrayParams)
    {
        if (!$this->mock) {
            $retVal = $this->commonCall(
                $this->package . ".ScriviDeroga",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        } else {
            $retVal = ['res' => [], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selConcDeroga($arrayParams)
    {
        if (!$this->mock) {
            $retVal = $this->commonCall(
                $this->package . ".selConcDeroga",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        } else {
            $retVal = ['res' => [], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selTipoConc($arrayParams)
    {

        if (!$this->mock) {
            $retVal = $this->commonCall(
                $this->package . ".selTipoConc",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        } else {
            $retVal = ['res' => [], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function CercaDeroga($arrayParams)
    {
        if (!$this->mock) {
            $retVal = $this->commonCall(
                $this->package . ".CercaDeroga",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        } else {
            $retVal = ['res' => [], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function getMockups()
    {
        $retVal = [];
        $retVal["selezioneConcessionari"] = $this->selezioneConcessionari(array(
            'tipo_conc'    => '',
            'anno'         => '',
            'semestre'     => '',
            'tipogioco'    => '',
            'tiporaccolta' => ''
        ));

        $retVal["selezioneTipoConcessionari"] = $this->selezioneTipoConcessionari(array('allIn' => '',));

        $retVal["result"] = $this->selezioneDatiTrasmessi(array(
            'tipo_concIn' => '',
            'cod_concIn'  => '',
            'annoIn'      => '',
            'semestreIn'  => '',
            'cod_giocoIn' => '',
            'tipo_reteIn' => '',
            'DWHFlag'     => '',
            'cfaamsIn'    => '',
            'subarea'     => 1,
        ));

        // $retVal["retrieve_prod"] = $this->retrieve_prod("A", "14"); //$P_COD_MITT = null, $P_PK_SG = null)
        // $retVal["retrieveSg"] = $this->retrieveSg("A"); //$COD_MITT = null, $P_PK_PROD = null)
        // $retVal["retrievePrimoAnnoValido"] = $this->retrievePrimoAnnoValido("A", "14"); //$COD_MITT = null, $P_PK_SG = null)
        // $retVal["verifyCreazioneFlusso"] = $this->verifyCreazioneFlusso("A", "14", "2015");
        //$retVal["creaFlusso"] = $this->creaFlusso("A", "14", "01/01/2020", "1", "2015", "01/02/2020", "03/02/2020", "", "", ""); //$P_COD_MITT, $P_PROG_SISTEMA_GIOCO_ID, $p_data_fine_invio607, $p_numero_totale_607, $p_anno600, $p_data_ritrasm_600, $p_data_replica, $filename_content, $file_allegato_content, $p_cf_operatore)
        // $retVal["retrieveFlussi"] = $this->retrieveFlussi(null, null, null, null); //$COD_MITT, $COD_PROD, $P_PK_SG, $statoFlusso)
        //  $retVal["retrieveFlussiCruscotto"] = $this->retrieveFlussiCruscotto();
        //  $retVal["retrieveFlusso"] = $this->retrieveFlusso(46); //$p_pk_607_flusso)
        //  $retVal["retrieve_607_ok"] = $this->retrieve_607_ok(46); //$p_pk_607_flusso)
        //  $retVal["retrieve_607_nok"] = $this->retrieve_607_nok(46); //$p_pk_607_flusso)
        //  $retVal["retrieve_num_600_annullati"] = $this->retrieve_num_600_annullati(46); //$p_pk_607_flusso)

        //$retVal["report_600_annullati"] = $this->report_600_annullati(null, null, 46); //$p_periodo, $p_tipo_componente, $p_pk_607_flusso)

        //  $retVal["retrieve_num_600_ritrasm"] = $this->retrieve_num_600_ritrasm(46); //$p_pk_607_flusso)
        //  $retVal["retrieve_num_600_non_ritrasm"] = $this->retrieve_num_600_non_ritrasm(46); //$p_pk_607_flusso)

        // $retVal["report_600_ritrasmessi"] = $this->report_600_ritrasmessi(null, null, 46); //$p_periodo, $p_tipo_componente, $p_pk_607_flusso)
        //$retVal["report_600_non_ritrasmessi"] = $this->report_600_non_ritrasmessi(null, null, 46); //$p_periodo, $p_tipo_componente, $p_pk_607_flusso)
        return $retVal;
    }
}
