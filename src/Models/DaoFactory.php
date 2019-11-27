<?php

namespace NIM_Backend\Models;

use NIM_Backend\SharedLibs\DBManager;

class DaoFactory
{

    protected $logger;
    protected $package;
    protected $nomignolo;
    protected $msgError;

    public function __construct(\Monolog\Logger $logger, $dbMan)
    {
        global $appConfig;

        $this->config = $appConfig;
        $this->logger = $logger;
        $this->package = "VLT_PKG_FLUSSO_607"; //$package;
        $this->nomignolo = "VLT_AMM";
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

    public function retrieveListConc()
    {
        $retVal = $this->commonCall(
            $this->package . ".RETRIEVE_LIST_CONC",
            null,
            ['P_RECORDSET', 'ERRCODE', 'ERRTEXT'],
            null,
            null,
            ['P_RECORDSET']
        );
        return (isset($retVal)) ? $retVal['P_RECORDSET'] : null;
    }
    public function retrieve_prod($P_COD_MITT = null, $P_PK_SG = null)
    {
        $retVal = $this->commonCall(
            $this->package . ".RETRIEVE_PROD",
            ['P_COD_MITT' => $P_COD_MITT, 'P_PK_SG' => $P_PK_SG],
            ['P_RECORDSET', 'ERRCODE', 'ERRTEXT'],
            null,
            null,
            ['P_RECORDSET']
        );
        return (isset($retVal)) ? $retVal['P_RECORDSET'] : null;
    }
    public function retrieveSg($COD_MITT = null, $P_PK_PROD = null)
    {
        $retVal = $this->commonCall(
            $this->package . ".retrieve_sg",
            ['P_COD_MITT' => $COD_MITT, 'P_pk_prod' => $P_PK_PROD],
            ['P_RECORDSET', 'ERRCODE', 'ERRTEXT'],
            null,
            null,
            ['P_RECORDSET']
        );
        return (isset($retVal)) ? $retVal['P_RECORDSET'] : null;
    }


    public function retrievePrimoAnnoValido($COD_MITT = null, $P_PK_SG = null)
    {
        $retVal = $this->commonCall(
            $this->package . ".RETRIEVE_PRIMO_ANNO_VALIDO",
            ['P_COD_MITT' => $COD_MITT, 'P_PK_SG' => $P_PK_SG],
            ['P_anno', 'ERRCODE', 'ERRTEXT'],
            null,
            null,
            null
        );
        return (isset($retVal)) ? $retVal['P_anno'] : null;
    }

    public function verifyCreazioneFlusso($COD_MITT, $PK_SG, $P_ANNO600)
    {
        $retVal = $this->commonCall(
            $this->package . ".VERIFY_CREAZIONE_FLUSSO",
            ['P_COD_MITT' => $COD_MITT, 'P_PK_SG' => $PK_SG, 'P_ANNO600' => $P_ANNO600],
            ['ERRCODE', 'ERRTEXT'],
            null,
            null,
            null
        );
        return $retVal;
    }

    public function verifyExtendFlusso($p_pk_607_flusso, $p_numero_totale_607)
    {
        $retVal = $this->commonCall(
            $this->package . ".verify_extend_flusso",
            ['p_pk_607_flusso' => $p_pk_607_flusso, 'p_numero_totale_607' => $p_numero_totale_607],
            ['ERRCODE', 'ERRTEXT'],
            null,
            null,
            null
        );
        return $retVal;
    }

    public function creaFlusso($P_COD_MITT, $P_PROG_SISTEMA_GIOCO_ID, $p_data_fine_invio607, $p_numero_totale_607, $p_anno600, $p_data_ritrasm_600, $p_data_replica, $filename_content, $file_allegato_content, $p_cf_operatore)
    {
        $retVal = $this->commonCall(
            $this->package . ".INSERT_CREAZIONE_FLUSSO",
            [
                'p_cod_mitt'            => $P_COD_MITT,
                'p_pk_sg'               => $P_PROG_SISTEMA_GIOCO_ID,
                'p_data_fine_invio607'  => $p_data_fine_invio607,
                'p_numero_totale_607'   => $p_numero_totale_607,
                'p_anno600'             => $p_anno600,
                'p_data_ritrasm_600'    => $p_data_ritrasm_600,
                'p_data_replica'        => $p_data_replica,
                'filename_content'      => $filename_content,
                'p_richiesta_conc'      => $file_allegato_content,
                'p_cf_operatore'        => $p_cf_operatore
            ],
            ['p_pk_607_flusso', 'ERRCODE', 'ERRTEXT'],
            ['p_richiesta_conc'],
            null,
            null,
        );
        return (isset($retVal)) ? $retVal['p_pk_607_flusso'] : null;
    }

    public function update_extend_flusso($p_pk_607_flusso, $p_data_fine_invio607, $p_numero_totale_607, $p_data_ritrasm_600, $p_data_replica)
    {
        $retVal = $this->commonCall(
            $this->package . ".update_extend_flusso",
            [
                'p_pk_607_flusso'      => $p_pk_607_flusso,
                'p_data_fine_invio607' => $p_data_fine_invio607,
                'p_numero_totale_607'  => $p_numero_totale_607,
                'p_data_ritrasm_600'   => $p_data_ritrasm_600,
                'p_data_replica'       => $p_data_replica
            ],
            ['ERRCODE', 'ERRTEXT'],
            null,
            null,
            null
        );
        return (isset($retVal)) ? $retVal['ERRCODE'] : null;
    }

    public function retrieveFlussi($COD_MITT, $COD_PROD, $P_PK_SG, $statoFlusso)
    {

        $retVal = $this->commonCall(
            $this->package . ".retrieve_lista_flussi",
            [
                'p_cod_mitt' => $COD_MITT,
                'p_pk_sg'    => $P_PK_SG,
                'p_pk_prod'  => $COD_PROD,
                'p_stato'    => $statoFlusso
            ],
            ['P_RECORDSET', 'ERRCODE', 'ERRTEXT'],
            null,
            null,
            ['P_RECORDSET']
        );
        return (isset($retVal)) ? $retVal['P_RECORDSET'] : null;
    }

    public function retrieveFlussiCruscotto()
    {
        $retVal = $this->commonCall(
            $this->package . ".retrieve_cruscotto_flusso",
            null,
            ['P_REC', 'ERRCODE', 'ERRTEXT'],
            null,
            null,
            ['P_REC']
        );
        return (isset($retVal)) ? $retVal['P_REC'] : null;
    }

    public function retrieveFlusso($p_pk_607_flusso)
    {
        $retVal = $this->commonCall(
            $this->package . ".retrieve_flusso",
            ['p_pk_607_flusso' => $p_pk_607_flusso],
            ['P_RECORDSET', 'ERRCODE', 'ERRTEXT'],
            null,
            null,
            ['P_RECORDSET']
        );
        return (isset($retVal)) ? $retVal['P_RECORDSET'] : null;
    }

    public function updateCloseFlusso($p_pk_607_flusso, $p_stato)
    {
        $retVal = $this->commonCall(
            $this->package . ".update_close_flusso",
            [
                'p_pk_607_flusso' => $p_pk_607_flusso,
                'p_stato'         => $p_stato
            ],
            ['ERRCODE', 'ERRTEXT'],
            null,
            null,
            null
        );
        return (isset($retVal) && !$retVal['ERRCODE']) ? 1 : 0;
    }

    public function ritrieve_richiesta_conc($p_pk_607_flusso, &$fileName, &$fileContent)
    {
        $retVal = $this->commonCall(
            $this->package . ".retrieve_richiesta_conc",
            ['p_pk_607_flusso' => $p_pk_607_flusso],
            ['p_RICK_BLOB', 'p_filename', 'ERRCODE', 'ERRTEXT'],
            null,
            ['p_RICK_BLOB'],
            null
        );

        $esito = 0;
        $fileContent = null;
        $fileName = null;
        if (isset($retVal) && !$retVal['ERRCODE']) {
            $esito = 1;
            $fileName = $retVal['p_filename'];
            $fileContent = $retVal['p_RICK_BLOB'];
        }
        return $esito;
    }

    /******************** Procedure per il dettaglio messaggi 600  **********************/

    public function retrieve_607_ok($p_pk_607_flusso)
    {
        $retVal = $this->commonCall(
            $this->package . ".retrieve_607_ok",
            ['p_pk_607_flusso' => $p_pk_607_flusso],
            ['P_REC', 'ERRCODE', 'ERRTEXT'],
            null,
            null,
            ['P_REC']
        );
        return (isset($retVal)) ? $retVal['P_REC'] : null;
    }

    public function retrieve_607_nok($p_pk_607_flusso)
    {
        $retVal = $this->commonCall(
            $this->package . ".retrieve_607_nok",
            ['p_pk_607_flusso' => $p_pk_607_flusso],
            ['P_REC', 'ERRCODE', 'ERRTEXT'],
            null,
            null,
            ['P_REC']
        );
        return (isset($retVal)) ? $retVal['P_REC'] : null;
    }

    /******************** Procedure per il dettaglio messaggi 600  **********************/

    /******************** Procedure per il dettaglio messaggi 607  **********************/

    public function retrieve_num_600_annullati($p_pk_607_flusso)
    {
        $retVal = $this->commonCall(
            $this->package . ".retrieve_num_600_annullati",
            ['p_pk_607_flusso' => $p_pk_607_flusso],
            ['P_REC', 'ERRCODE', 'ERRTEXT'],
            null,
            null,
            ['P_REC']
        );
        return (isset($retVal)) ? $retVal['P_REC'] : null;
    }

    public function report_600_annullati($p_periodo, $p_tipo_componente, $p_pk_607_flusso)
    {
        $retVal = $this->commonCall(
            $this->package . ".report_600_annullati",
            [
                'p_periodo'          => $p_periodo,
                'p_tipo_componente'  => $p_tipo_componente,
                'p_pk_607_flusso'    => $p_pk_607_flusso
            ],
            ['DATASET', 'ERRCODE', 'ERRTEXT'],
            null,
            null,
            ['DATASET']
        );
        return (isset($retVal)) ? $retVal['DATASET'] : null;
    }

    /******************** Procedure per il dettaglio messaggi 607  **********************/

    /******************** Procedure per il dettaglio messaggi 607 ritrasmessi **********************/

    public function retrieve_num_600_ritrasm($p_pk_607_flusso)
    {
        $retVal = $this->commonCall(
            $this->package . ".retrieve_num_600_ritrasm",
            ['p_pk_607_flusso' => $p_pk_607_flusso],
            ['P_REC', 'ERRCODE', 'ERRTEXT'],
            null,
            null,
            ['P_REC']
        );
        return (isset($retVal)) ? $retVal['P_REC'] : null;
    }

    public function retrieve_num_600_non_ritrasm($p_pk_607_flusso)
    {
        $retVal = $this->commonCall(
            $this->package . ".retrieve_num_600_non_ritrasm",
            ['p_pk_607_flusso' => $p_pk_607_flusso],
            ['P_REC', 'ERRCODE', 'ERRTEXT'],
            null,
            null,
            ['P_REC']
        );
        return (isset($retVal)) ? $retVal['P_REC'] : null;
    }

    public function report_600_ritrasmessi($p_periodo, $p_tipo_componente, $p_pk_607_flusso)
    {
        $retVal = $this->commonCall(
            $this->package . ".report_600_ritrasmessi",
            [
                'p_periodo'          => $p_periodo,
                'p_tipo_componente'  => $p_tipo_componente,
                'p_pk_607_flusso'    => $p_pk_607_flusso
            ],
            ['DATASET', 'ERRCODE', 'ERRTEXT'],
            null,
            null,
            ['DATASET']
        );
        return (isset($retVal)) ? $retVal['DATASET'] : null;
    }

    public function report_600_non_ritrasmessi($p_periodo, $p_tipo_componente, $p_pk_607_flusso)
    {
        $retVal = $this->commonCall(
            $this->package . ".report_600_non_ritrasmessi",
            [
                'p_periodo'          => $p_periodo,
                'p_tipo_componente'  => $p_tipo_componente,
                'p_pk_607_flusso'    => $p_pk_607_flusso
            ],
            ['DATASET', 'ERRCODE', 'ERRTEXT'],
            null,
            null,
            ['DATASET']
        );
        return (isset($retVal)) ? $retVal['DATASET'] : null;
    }
}
