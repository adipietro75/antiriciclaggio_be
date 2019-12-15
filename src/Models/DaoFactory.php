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
        if (!$this->mock) 
        {
            $retVal = $this->commonCall(
                $this->package . ".selConcNew",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        }
        else
        {
            $retVal = ['P_RECORDSET' => [
                ["COD_MITT" => "A", "DENOMINAZIONE" => "CIRSA ITALIA S.P.A."], ["COD_MITT" => "B", "DENOMINAZIONE" => "SISAL ENTERTAINMENT S.P.A."], ["COD_MITT" => "C", "DENOMINAZIONE" => "LOTTOMATICA VIDEOLOT RETE S.P.A."], ["COD_MITT" => "D", "DENOMINAZIONE" => "ADMIRAL GAMING NETWORK S.R.L."], ["COD_MITT" => "E", "DENOMINAZIONE" => "CODERE NETWORK S.P.A."], ["COD_MITT" => "F", "DENOMINAZIONE" => "HBG CONNEX SPA"], ["COD_MITT" => "G", "DENOMINAZIONE" => "GLOBAL STARNET LIMITED"], ["COD_MITT" => "H", "DENOMINAZIONE" => "GAMENET S.P.A."], ["COD_MITT" => "I", "DENOMINAZIONE" => "COGETECH S.P.A."], ["COD_MITT" => "L", "DENOMINAZIONE" => "SNAITECH SPA"], ["COD_MITT" => "M", "DENOMINAZIONE" => "NETWIN ITALIA S.P.A."], ["COD_MITT" => "N", "DENOMINAZIONE" => "NTS NETWORK S.P.A."], ["COD_MITT" => "P", "DENOMINAZIONE" => "INTRALOT GAMING MACHINES S.P.A."]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }

        if ($retVal == null) {
            $this->msgError = 'Non sono presenti concessionari per i parametri di ';
            $this->msgError .= 'ricerca selezionati';
        }        

       
        return (isset($retVal)) ? $retVal['P_RECORDSET'] : null;
    }

    public function selezioneTipoConcessionari($arrayParams)
    {
        if (!$this->mock) 
        {
            $retVal = $this->commonCall(
                $this->package . ".selTipoConc",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        }
        else
        {
            $retVal = ['P_RECORDSET' => [
                ["COD_MITT" => "A", "DENOMINAZIONE" => "CIRSA ITALIA S.P.A."], ["COD_MITT" => "B", "DENOMINAZIONE" => "SISAL ENTERTAINMENT S.P.A."], ["COD_MITT" => "C", "DENOMINAZIONE" => "LOTTOMATICA VIDEOLOT RETE S.P.A."], ["COD_MITT" => "D", "DENOMINAZIONE" => "ADMIRAL GAMING NETWORK S.R.L."], ["COD_MITT" => "E", "DENOMINAZIONE" => "CODERE NETWORK S.P.A."], ["COD_MITT" => "F", "DENOMINAZIONE" => "HBG CONNEX SPA"], ["COD_MITT" => "G", "DENOMINAZIONE" => "GLOBAL STARNET LIMITED"], ["COD_MITT" => "H", "DENOMINAZIONE" => "GAMENET S.P.A."], ["COD_MITT" => "I", "DENOMINAZIONE" => "COGETECH S.P.A."], ["COD_MITT" => "L", "DENOMINAZIONE" => "SNAITECH SPA"], ["COD_MITT" => "M", "DENOMINAZIONE" => "NETWIN ITALIA S.P.A."], ["COD_MITT" => "N", "DENOMINAZIONE" => "NTS NETWORK S.P.A."], ["COD_MITT" => "P", "DENOMINAZIONE" => "INTRALOT GAMING MACHINES S.P.A."]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }


        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selezioneDatiTrasmessi($arrayParams)
    {
        if (!$this->mock) 
        {
            $retVal = $this->commonCall(
                $this->package . ".selDatiTrasmessi",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        }
        else
        {
            $retVal = ['P_RECORDSET' => [
                ["COD_MITT" => "A", "DENOMINAZIONE" => "CIRSA ITALIA S.P.A."], ["COD_MITT" => "B", "DENOMINAZIONE" => "SISAL ENTERTAINMENT S.P.A."], ["COD_MITT" => "C", "DENOMINAZIONE" => "LOTTOMATICA VIDEOLOT RETE S.P.A."], ["COD_MITT" => "D", "DENOMINAZIONE" => "ADMIRAL GAMING NETWORK S.R.L."], ["COD_MITT" => "E", "DENOMINAZIONE" => "CODERE NETWORK S.P.A."], ["COD_MITT" => "F", "DENOMINAZIONE" => "HBG CONNEX SPA"], ["COD_MITT" => "G", "DENOMINAZIONE" => "GLOBAL STARNET LIMITED"], ["COD_MITT" => "H", "DENOMINAZIONE" => "GAMENET S.P.A."], ["COD_MITT" => "I", "DENOMINAZIONE" => "COGETECH S.P.A."], ["COD_MITT" => "L", "DENOMINAZIONE" => "SNAITECH SPA"], ["COD_MITT" => "M", "DENOMINAZIONE" => "NETWIN ITALIA S.P.A."], ["COD_MITT" => "N", "DENOMINAZIONE" => "NTS NETWORK S.P.A."], ["COD_MITT" => "P", "DENOMINAZIONE" => "INTRALOT GAMING MACHINES S.P.A."]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }

        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selezioneDatiTrasmessiCSV($arrayParams)
    {
        if (!$this->mock) 
        {
            $retVal = $this->commonCall(
                $this->package . ".selDatiTrasmessiCSV",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        }
        else
        {
            $retVal = ['P_RECORDSET' => [
                ["COD_MITT" => "A", "DENOMINAZIONE" => "CIRSA ITALIA S.P.A."], ["COD_MITT" => "B", "DENOMINAZIONE" => "SISAL ENTERTAINMENT S.P.A."], ["COD_MITT" => "C", "DENOMINAZIONE" => "LOTTOMATICA VIDEOLOT RETE S.P.A."], ["COD_MITT" => "D", "DENOMINAZIONE" => "ADMIRAL GAMING NETWORK S.R.L."], ["COD_MITT" => "E", "DENOMINAZIONE" => "CODERE NETWORK S.P.A."], ["COD_MITT" => "F", "DENOMINAZIONE" => "HBG CONNEX SPA"], ["COD_MITT" => "G", "DENOMINAZIONE" => "GLOBAL STARNET LIMITED"], ["COD_MITT" => "H", "DENOMINAZIONE" => "GAMENET S.P.A."], ["COD_MITT" => "I", "DENOMINAZIONE" => "COGETECH S.P.A."], ["COD_MITT" => "L", "DENOMINAZIONE" => "SNAITECH SPA"], ["COD_MITT" => "M", "DENOMINAZIONE" => "NETWIN ITALIA S.P.A."], ["COD_MITT" => "N", "DENOMINAZIONE" => "NTS NETWORK S.P.A."], ["COD_MITT" => "P", "DENOMINAZIONE" => "INTRALOT GAMING MACHINES S.P.A."]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selezioneGiochi($arrayParams)
    {
        if (!$this->mock) 
        {
            $retVal = $this->commonCall(
                $this->package . ".selGiochi",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        }
        else
        {
            $retVal = ['P_RECORDSET' => [
                ["COD_MITT" => "A", "DENOMINAZIONE" => "CIRSA ITALIA S.P.A."], ["COD_MITT" => "B", "DENOMINAZIONE" => "SISAL ENTERTAINMENT S.P.A."], ["COD_MITT" => "C", "DENOMINAZIONE" => "LOTTOMATICA VIDEOLOT RETE S.P.A."], ["COD_MITT" => "D", "DENOMINAZIONE" => "ADMIRAL GAMING NETWORK S.R.L."], ["COD_MITT" => "E", "DENOMINAZIONE" => "CODERE NETWORK S.P.A."], ["COD_MITT" => "F", "DENOMINAZIONE" => "HBG CONNEX SPA"], ["COD_MITT" => "G", "DENOMINAZIONE" => "GLOBAL STARNET LIMITED"], ["COD_MITT" => "H", "DENOMINAZIONE" => "GAMENET S.P.A."], ["COD_MITT" => "I", "DENOMINAZIONE" => "COGETECH S.P.A."], ["COD_MITT" => "L", "DENOMINAZIONE" => "SNAITECH SPA"], ["COD_MITT" => "M", "DENOMINAZIONE" => "NETWIN ITALIA S.P.A."], ["COD_MITT" => "N", "DENOMINAZIONE" => "NTS NETWORK S.P.A."], ["COD_MITT" => "P", "DENOMINAZIONE" => "INTRALOT GAMING MACHINES S.P.A."]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selElencoInadempienti($arrayParams)
    {
        if (!$this->mock) 
        {
            $retVal = $this->commonCall(
                $this->package . ".selElencoInadempienti",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        }
        else
        {
            $retVal = ['P_RECORDSET' => [
                ["COD_MITT" => "A", "DENOMINAZIONE" => "CIRSA ITALIA S.P.A."], ["COD_MITT" => "B", "DENOMINAZIONE" => "SISAL ENTERTAINMENT S.P.A."], ["COD_MITT" => "C", "DENOMINAZIONE" => "LOTTOMATICA VIDEOLOT RETE S.P.A."], ["COD_MITT" => "D", "DENOMINAZIONE" => "ADMIRAL GAMING NETWORK S.R.L."], ["COD_MITT" => "E", "DENOMINAZIONE" => "CODERE NETWORK S.P.A."], ["COD_MITT" => "F", "DENOMINAZIONE" => "HBG CONNEX SPA"], ["COD_MITT" => "G", "DENOMINAZIONE" => "GLOBAL STARNET LIMITED"], ["COD_MITT" => "H", "DENOMINAZIONE" => "GAMENET S.P.A."], ["COD_MITT" => "I", "DENOMINAZIONE" => "COGETECH S.P.A."], ["COD_MITT" => "L", "DENOMINAZIONE" => "SNAITECH SPA"], ["COD_MITT" => "M", "DENOMINAZIONE" => "NETWIN ITALIA S.P.A."], ["COD_MITT" => "N", "DENOMINAZIONE" => "NTS NETWORK S.P.A."], ["COD_MITT" => "P", "DENOMINAZIONE" => "INTRALOT GAMING MACHINES S.P.A."]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selProspetti($arrayParams)
    {
        if (!$this->mock) 
        {
            $retVal = $this->commonCall(
                $this->package . ".selProspetti",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        }
        else
        {
            $retVal = ['P_RECORDSET' => [
                ["COD_MITT" => "A", "DENOMINAZIONE" => "CIRSA ITALIA S.P.A."], ["COD_MITT" => "B", "DENOMINAZIONE" => "SISAL ENTERTAINMENT S.P.A."], ["COD_MITT" => "C", "DENOMINAZIONE" => "LOTTOMATICA VIDEOLOT RETE S.P.A."], ["COD_MITT" => "D", "DENOMINAZIONE" => "ADMIRAL GAMING NETWORK S.R.L."], ["COD_MITT" => "E", "DENOMINAZIONE" => "CODERE NETWORK S.P.A."], ["COD_MITT" => "F", "DENOMINAZIONE" => "HBG CONNEX SPA"], ["COD_MITT" => "G", "DENOMINAZIONE" => "GLOBAL STARNET LIMITED"], ["COD_MITT" => "H", "DENOMINAZIONE" => "GAMENET S.P.A."], ["COD_MITT" => "I", "DENOMINAZIONE" => "COGETECH S.P.A."], ["COD_MITT" => "L", "DENOMINAZIONE" => "SNAITECH SPA"], ["COD_MITT" => "M", "DENOMINAZIONE" => "NETWIN ITALIA S.P.A."], ["COD_MITT" => "N", "DENOMINAZIONE" => "NTS NETWORK S.P.A."], ["COD_MITT" => "P", "DENOMINAZIONE" => "INTRALOT GAMING MACHINES S.P.A."]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;


    }

    public function calcolaStatDett($arrayParams)
    {
        if (!$this->mock) 
        {
            $retVal = $this->commonCall(
                $this->package . ".calcolaStatDett",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        }
        else
        {
            $retVal = ['P_RECORDSET' => [
                ["COD_MITT" => "A", "DENOMINAZIONE" => "CIRSA ITALIA S.P.A."], ["COD_MITT" => "B", "DENOMINAZIONE" => "SISAL ENTERTAINMENT S.P.A."], ["COD_MITT" => "C", "DENOMINAZIONE" => "LOTTOMATICA VIDEOLOT RETE S.P.A."], ["COD_MITT" => "D", "DENOMINAZIONE" => "ADMIRAL GAMING NETWORK S.R.L."], ["COD_MITT" => "E", "DENOMINAZIONE" => "CODERE NETWORK S.P.A."], ["COD_MITT" => "F", "DENOMINAZIONE" => "HBG CONNEX SPA"], ["COD_MITT" => "G", "DENOMINAZIONE" => "GLOBAL STARNET LIMITED"], ["COD_MITT" => "H", "DENOMINAZIONE" => "GAMENET S.P.A."], ["COD_MITT" => "I", "DENOMINAZIONE" => "COGETECH S.P.A."], ["COD_MITT" => "L", "DENOMINAZIONE" => "SNAITECH SPA"], ["COD_MITT" => "M", "DENOMINAZIONE" => "NETWIN ITALIA S.P.A."], ["COD_MITT" => "N", "DENOMINAZIONE" => "NTS NETWORK S.P.A."], ["COD_MITT" => "P", "DENOMINAZIONE" => "INTRALOT GAMING MACHINES S.P.A."]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;


    }

    public function selElencoInadempientiNew($arrayParams)
    {
        if (!$this->mock) 
        {
            $retVal = $this->commonCall(
                $this->package . ".selElencoInadempientiNew",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        }
        else
        {
            $retVal = ['P_RECORDSET' => [
                ["COD_MITT" => "A", "DENOMINAZIONE" => "CIRSA ITALIA S.P.A."], ["COD_MITT" => "B", "DENOMINAZIONE" => "SISAL ENTERTAINMENT S.P.A."], ["COD_MITT" => "C", "DENOMINAZIONE" => "LOTTOMATICA VIDEOLOT RETE S.P.A."], ["COD_MITT" => "D", "DENOMINAZIONE" => "ADMIRAL GAMING NETWORK S.R.L."], ["COD_MITT" => "E", "DENOMINAZIONE" => "CODERE NETWORK S.P.A."], ["COD_MITT" => "F", "DENOMINAZIONE" => "HBG CONNEX SPA"], ["COD_MITT" => "G", "DENOMINAZIONE" => "GLOBAL STARNET LIMITED"], ["COD_MITT" => "H", "DENOMINAZIONE" => "GAMENET S.P.A."], ["COD_MITT" => "I", "DENOMINAZIONE" => "COGETECH S.P.A."], ["COD_MITT" => "L", "DENOMINAZIONE" => "SNAITECH SPA"], ["COD_MITT" => "M", "DENOMINAZIONE" => "NETWIN ITALIA S.P.A."], ["COD_MITT" => "N", "DENOMINAZIONE" => "NTS NETWORK S.P.A."], ["COD_MITT" => "P", "DENOMINAZIONE" => "INTRALOT GAMING MACHINES S.P.A."]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function ListaGiochi($arrayParams)
    {
        if (!$this->mock) 
        {
            $retVal = $this->commonCall(
                $this->package . ".ListaGiochi",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        }
        else
        {
            $retVal = ['P_RECORDSET' => [
                ["COD_MITT" => "A", "DENOMINAZIONE" => "CIRSA ITALIA S.P.A."], ["COD_MITT" => "B", "DENOMINAZIONE" => "SISAL ENTERTAINMENT S.P.A."], ["COD_MITT" => "C", "DENOMINAZIONE" => "LOTTOMATICA VIDEOLOT RETE S.P.A."], ["COD_MITT" => "D", "DENOMINAZIONE" => "ADMIRAL GAMING NETWORK S.R.L."], ["COD_MITT" => "E", "DENOMINAZIONE" => "CODERE NETWORK S.P.A."], ["COD_MITT" => "F", "DENOMINAZIONE" => "HBG CONNEX SPA"], ["COD_MITT" => "G", "DENOMINAZIONE" => "GLOBAL STARNET LIMITED"], ["COD_MITT" => "H", "DENOMINAZIONE" => "GAMENET S.P.A."], ["COD_MITT" => "I", "DENOMINAZIONE" => "COGETECH S.P.A."], ["COD_MITT" => "L", "DENOMINAZIONE" => "SNAITECH SPA"], ["COD_MITT" => "M", "DENOMINAZIONE" => "NETWIN ITALIA S.P.A."], ["COD_MITT" => "N", "DENOMINAZIONE" => "NTS NETWORK S.P.A."], ["COD_MITT" => "P", "DENOMINAZIONE" => "INTRALOT GAMING MACHINES S.P.A."]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function ElencoInvii($arrayParams)
    {

        if (!$this->mock) 
        {
            $retVal = $this->commonCall(
                $this->package . ".ElencoInvii",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        }
        else
        {
            $retVal = ['P_RECORDSET' => [
                ["COD_MITT" => "A", "DENOMINAZIONE" => "CIRSA ITALIA S.P.A."], ["COD_MITT" => "B", "DENOMINAZIONE" => "SISAL ENTERTAINMENT S.P.A."], ["COD_MITT" => "C", "DENOMINAZIONE" => "LOTTOMATICA VIDEOLOT RETE S.P.A."], ["COD_MITT" => "D", "DENOMINAZIONE" => "ADMIRAL GAMING NETWORK S.R.L."], ["COD_MITT" => "E", "DENOMINAZIONE" => "CODERE NETWORK S.P.A."], ["COD_MITT" => "F", "DENOMINAZIONE" => "HBG CONNEX SPA"], ["COD_MITT" => "G", "DENOMINAZIONE" => "GLOBAL STARNET LIMITED"], ["COD_MITT" => "H", "DENOMINAZIONE" => "GAMENET S.P.A."], ["COD_MITT" => "I", "DENOMINAZIONE" => "COGETECH S.P.A."], ["COD_MITT" => "L", "DENOMINAZIONE" => "SNAITECH SPA"], ["COD_MITT" => "M", "DENOMINAZIONE" => "NETWIN ITALIA S.P.A."], ["COD_MITT" => "N", "DENOMINAZIONE" => "NTS NETWORK S.P.A."], ["COD_MITT" => "P", "DENOMINAZIONE" => "INTRALOT GAMING MACHINES S.P.A."]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function ScriviDeroga($arrayParams)
    {
        if (!$this->mock) 
        {
            $retVal = $this->commonCall(
                $this->package . ".ScriviDeroga",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        }
        else
        {
            $retVal = ['P_RECORDSET' => [
                ["COD_MITT" => "A", "DENOMINAZIONE" => "CIRSA ITALIA S.P.A."], ["COD_MITT" => "B", "DENOMINAZIONE" => "SISAL ENTERTAINMENT S.P.A."], ["COD_MITT" => "C", "DENOMINAZIONE" => "LOTTOMATICA VIDEOLOT RETE S.P.A."], ["COD_MITT" => "D", "DENOMINAZIONE" => "ADMIRAL GAMING NETWORK S.R.L."], ["COD_MITT" => "E", "DENOMINAZIONE" => "CODERE NETWORK S.P.A."], ["COD_MITT" => "F", "DENOMINAZIONE" => "HBG CONNEX SPA"], ["COD_MITT" => "G", "DENOMINAZIONE" => "GLOBAL STARNET LIMITED"], ["COD_MITT" => "H", "DENOMINAZIONE" => "GAMENET S.P.A."], ["COD_MITT" => "I", "DENOMINAZIONE" => "COGETECH S.P.A."], ["COD_MITT" => "L", "DENOMINAZIONE" => "SNAITECH SPA"], ["COD_MITT" => "M", "DENOMINAZIONE" => "NETWIN ITALIA S.P.A."], ["COD_MITT" => "N", "DENOMINAZIONE" => "NTS NETWORK S.P.A."], ["COD_MITT" => "P", "DENOMINAZIONE" => "INTRALOT GAMING MACHINES S.P.A."]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selConcDeroga($arrayParams)
    {
        if (!$this->mock) 
        {
            $retVal = $this->commonCall(
                $this->package . ".selConcDeroga",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        }
        else
        {
            $retVal = ['P_RECORDSET' => [
                ["COD_MITT" => "A", "DENOMINAZIONE" => "CIRSA ITALIA S.P.A."], ["COD_MITT" => "B", "DENOMINAZIONE" => "SISAL ENTERTAINMENT S.P.A."], ["COD_MITT" => "C", "DENOMINAZIONE" => "LOTTOMATICA VIDEOLOT RETE S.P.A."], ["COD_MITT" => "D", "DENOMINAZIONE" => "ADMIRAL GAMING NETWORK S.R.L."], ["COD_MITT" => "E", "DENOMINAZIONE" => "CODERE NETWORK S.P.A."], ["COD_MITT" => "F", "DENOMINAZIONE" => "HBG CONNEX SPA"], ["COD_MITT" => "G", "DENOMINAZIONE" => "GLOBAL STARNET LIMITED"], ["COD_MITT" => "H", "DENOMINAZIONE" => "GAMENET S.P.A."], ["COD_MITT" => "I", "DENOMINAZIONE" => "COGETECH S.P.A."], ["COD_MITT" => "L", "DENOMINAZIONE" => "SNAITECH SPA"], ["COD_MITT" => "M", "DENOMINAZIONE" => "NETWIN ITALIA S.P.A."], ["COD_MITT" => "N", "DENOMINAZIONE" => "NTS NETWORK S.P.A."], ["COD_MITT" => "P", "DENOMINAZIONE" => "INTRALOT GAMING MACHINES S.P.A."]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function selTipoConc($arrayParams)
    {

        if (!$this->mock) 
        {
            $retVal = $this->commonCall(
                $this->package . ".selTipoConc",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        }
        else
        {
            $retVal = ['P_RECORDSET' => [
                ["COD_MITT" => "A", "DENOMINAZIONE" => "CIRSA ITALIA S.P.A."], ["COD_MITT" => "B", "DENOMINAZIONE" => "SISAL ENTERTAINMENT S.P.A."], ["COD_MITT" => "C", "DENOMINAZIONE" => "LOTTOMATICA VIDEOLOT RETE S.P.A."], ["COD_MITT" => "D", "DENOMINAZIONE" => "ADMIRAL GAMING NETWORK S.R.L."], ["COD_MITT" => "E", "DENOMINAZIONE" => "CODERE NETWORK S.P.A."], ["COD_MITT" => "F", "DENOMINAZIONE" => "HBG CONNEX SPA"], ["COD_MITT" => "G", "DENOMINAZIONE" => "GLOBAL STARNET LIMITED"], ["COD_MITT" => "H", "DENOMINAZIONE" => "GAMENET S.P.A."], ["COD_MITT" => "I", "DENOMINAZIONE" => "COGETECH S.P.A."], ["COD_MITT" => "L", "DENOMINAZIONE" => "SNAITECH SPA"], ["COD_MITT" => "M", "DENOMINAZIONE" => "NETWIN ITALIA S.P.A."], ["COD_MITT" => "N", "DENOMINAZIONE" => "NTS NETWORK S.P.A."], ["COD_MITT" => "P", "DENOMINAZIONE" => "INTRALOT GAMING MACHINES S.P.A."]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function CercaDeroga($arrayParams)
    {
        if (!$this->mock) 
        {
            $retVal = $this->commonCall(
                $this->package . ".CercaDeroga",
                $arrayParams,
                ['res', 'ERR', 'ERRCODE'],
                null,
                null,
                ['res']
            );
        }
        else
        {
            $retVal = ['P_RECORDSET' => [
                ["COD_MITT" => "A", "DENOMINAZIONE" => "CIRSA ITALIA S.P.A."], ["COD_MITT" => "B", "DENOMINAZIONE" => "SISAL ENTERTAINMENT S.P.A."], ["COD_MITT" => "C", "DENOMINAZIONE" => "LOTTOMATICA VIDEOLOT RETE S.P.A."], ["COD_MITT" => "D", "DENOMINAZIONE" => "ADMIRAL GAMING NETWORK S.R.L."], ["COD_MITT" => "E", "DENOMINAZIONE" => "CODERE NETWORK S.P.A."], ["COD_MITT" => "F", "DENOMINAZIONE" => "HBG CONNEX SPA"], ["COD_MITT" => "G", "DENOMINAZIONE" => "GLOBAL STARNET LIMITED"], ["COD_MITT" => "H", "DENOMINAZIONE" => "GAMENET S.P.A."], ["COD_MITT" => "I", "DENOMINAZIONE" => "COGETECH S.P.A."], ["COD_MITT" => "L", "DENOMINAZIONE" => "SNAITECH SPA"], ["COD_MITT" => "M", "DENOMINAZIONE" => "NETWIN ITALIA S.P.A."], ["COD_MITT" => "N", "DENOMINAZIONE" => "NTS NETWORK S.P.A."], ["COD_MITT" => "P", "DENOMINAZIONE" => "INTRALOT GAMING MACHINES S.P.A."]
            ], 'ERRCODE' => 0, 'ERRTEXT' => ''];
        }
        return (isset($retVal) && isset($retVal['res'])) ? $retVal['res'] : null;
    }

    public function getMockups()
    {
        $retVal = [];
        //$retVal["retrieveListConc"] = $this->retrieveListConc();
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
