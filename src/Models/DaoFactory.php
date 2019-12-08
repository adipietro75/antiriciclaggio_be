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

        if ($rst['ERRCODE'] != 0) {
            $this->msgError = $rst['ERR'];
            $this->logger->error($rst['ERR']);
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
        $retVal = $this->commonCall(
            $this->package . ".selConcNew",
            $arrayParams,
            ['res', 'ERR', 'ERRCODE'],
            null,
            null,
            ['res']
        );

        if ($retVal == null) {
            $this->msgError = 'Non sono presenti concessionari per i parametri di ';
            $this->msgError .= 'ricerca selezionati';
        }        

        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selezioneTipoConcessionari($arrayParams)
    {
        $retVal = $this->commonCall(
            $this->package . ".selTipoConc",
            $arrayParams,
            ['res', 'ERR', 'ERRCODE'],
            null,
            null,
            ['res']
        );

        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selezioneDatiTrasmessi($arrayParams)
    {
        $retVal = $this->commonCall(
            $this->package . ".selDatiTrasmessi",
            $arrayParams,
            ['res', 'ERR', 'ERRCODE'],
            null,
            null,
            ['res']
        );

        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selezioneDatiTrasmessiCSV($arrayParams)
    {
        $retVal = $this->commonCall(
            $this->package . ".selDatiTrasmessiCSV",
            $arrayParams,
            ['res', 'ERR', 'ERRCODE'],
            null,
            null,
            ['res']
        );

        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selezioneGiochi($arrayParams)
    {
        $retVal = $this->commonCall(
            $this->package . ".selGiochi",
            $arrayParams,
            ['res', 'ERR', 'ERRCODE'],
            null,
            null,
            ['res']
        );

        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selElencoInadempienti($arrayParams)
    {
        $retVal = $this->commonCall(
            $this->package . ".selElencoInadempienti",
            $arrayParams,
            ['res', 'ERR', 'ERRCODE'],
            null,
            null,
            ['res']
        );

        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selProspetti($arrayParams)
    {
        $retVal = $this->commonCall(
            $this->package . ".selProspetti",
            $arrayParams,
            ['res', 'ERR', 'ERRCODE'],
            null,
            null,
            ['res']
        );

        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function calcolaStatDett($arrayParams)
    {
        $retVal = $this->commonCall(
            $this->package . ".calcolaStatDett",
            $arrayParams,
            ['res', 'ERR', 'ERRCODE'],
            null,
            null,
            ['res']
        );

        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selElencoInadempientiNew($arrayParams)
    {
        $retVal = $this->commonCall(
            $this->package . ".selElencoInadempientiNew",
            $arrayParams,
            ['res', 'ERR', 'ERRCODE'],
            null,
            null,
            ['res']
        );

        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function ListaGiochi($arrayParams)
    {
        $retVal = $this->commonCall(
            $this->package . ".ListaGiochi",
            $arrayParams,
            ['res', 'ERR', 'ERRCODE'],
            null,
            null,
            ['res']
        );

        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function ElencoInvii($arrayParams)
    {
        $retVal = $this->commonCall(
            $this->package . ".ElencoInvii",
            $arrayParams,
            ['res', 'ERR', 'ERRCODE'],
            null,
            null,
            ['res']
        );

        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }
}
