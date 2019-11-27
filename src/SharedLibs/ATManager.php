<?php
/**
 * Layer di Astrazione tra le applicazioni online e i servizi di Anagrafe Tributaria AUDM
 *
 * PHP version 7.x | PHPSlim 3.x
 *
 * @package ATManager
 * @author  Original Author <f.santini@almaviva.it>
 * @version 2.0 2019-10-15
 */

namespace NIM_Backend\SharedLibs;

require_once 'include/clientAUDM.php';
require_once 'include/modelSoggetto.php';

class ATManager
{
    protected $logger;

    public function __construct(\Monolog\Logger $logger)
    {
        global $appConfig;

        $this->logger = $logger;
        $this->config = $appConfig;
    }

    public function getSoggetto($cf, $applicazione=6, $poi=1)
    {
        if ($this->config['debug'] === true) {
            $this->logger->info('ATManager - CodiceFiscale Soggetto: ' . $cf);
        }

        $clientAUDM      = new \servizioWebAUDM();
        $soggettoDaAUDM  = $clientAUDM->getInfoSoggettoFiscale($cf, $applicazione, $poi);
        $codiceDiRitorno = $soggettoDaAUDM->_return->messaggio->codice;

        if($soggettoDaAUDM->_return->messaggio->codice != '200') {
            $this->logger->warning('getSoggetto codiceDiRitorno: ' . $codiceDiRitorno);
            $this->logger->warning('getSoggetto: dati soggetto: ', $soggettoDaAUDM);
            return false;
        }

        $soggetto = new \modelSoggetto();

        $personaFisica   = $soggettoDaAUDM->_return->personaFisica;
        $soggettoDiverso = $soggettoDaAUDM->_return->soggettoDiverso;

        if(!empty($personaFisica)) {
            $soggetto->setTipo(\modelSoggetto::$tipoPersonaFisica);
            $soggetto->setCodiceFiscale($personaFisica->codiceFiscale);
            $soggetto->setVersione($personaFisica->versione);
            $soggetto->setCognome($personaFisica->cognome);
            $soggetto->setNome($personaFisica->nome);
            $soggetto->setSesso($personaFisica->sesso);
            $soggetto->setComuneDiNascita($personaFisica->comuneNascita);
            $soggetto->setProvinciaDiNascita($personaFisica->provinciaNascita);
            $soggetto->setStatoDiNascita('ITALIA');

            if($soggetto->getProvinciaDiNascita() == 'EE'){
                $soggetto->setStatoDiNascita($soggetto->getComuneDiNascita());
                $soggetto->setComuneDiNascita('');
            }

            $soggetto->setDataDiNascita($personaFisica->dataNascita);
            $stato = $personaFisica->dittaIndivStato;

            //if ($stato == 'ATTIVA' || $stato == 'SOSPESA') {//Se presente il domicilio fiscale in "altri dati" e' prioritario
            if(!empty($personaFisica->dittaIndivPiva)) {
                if(empty($personaFisica->dittaIndivMotivoCessazione)) {
                    $soggetto->setTipo(\modelSoggetto::$tipoDittaIndividuale);
                    $soggetto->setSedeLegaleToponimo($personaFisica->dittaIndivLuogoEseIndirizzo);
                    $soggetto->setSedeLegaleComune($personaFisica->dittaIndivLuogoEseComune);
                    $soggetto->setSedeLegaleCap($personaFisica->dittaIndivLuogoEseCap);
                    $soggetto->setSedeLegaleProvincia($personaFisica->dittaIndivLuogoEseProvincia);
                    $soggetto->setTipo(\modelSoggetto::$tipoDittaIndividuale);
                } else {
                    $soggetto->setTipo(\modelSoggetto::$tipoPersonaFisica);
                    $soggetto->setSedeLegaleToponimo($personaFisica->dittaIndivLuogoEseIndirizzo);
                    $soggetto->setSedeLegaleComune($personaFisica->dittaIndivLuogoEseComune);
                    $soggetto->setSedeLegaleCap($personaFisica->dittaIndivLuogoEseCap);
                    $soggetto->setSedeLegaleProvincia($personaFisica->dittaIndivLuogoEseProvincia);
                }

                $soggetto->setDescrizioneDenominazione($personaFisica->dittaIndivDenominazione);

                if($personaFisica->dittaIndivPiva=="-") {
                    $soggetto->setNumeroPartitaIva(NULL);
                } else {
                    $soggetto->setNumeroPartitaIva($personaFisica->dittaIndivPiva);
                }

                $soggetto->setStato($stato);

                $soggetto->setDomicilioFiscaleToponimo($personaFisica->domFiscIndirizzo);
                $soggetto->setDomicilioFiscaleComune($personaFisica->domFiscComune);
                $soggetto->setDomicilioFiscaleCap($personaFisica->dittaIndivLuogoEseCap);
                $soggetto->setDomicilioFiscaleProvincia($personaFisica->dittaIndivLuogoEseProvincia);

                $this->logger->info('getSoggetto : Passo Persona Ditta Individuale '.$cf.' Den: ', $soggetto->setDescrizioneDenominazione);
            } else {
                $soggetto->setTipo(\modelSoggetto::$tipoPersonaFisica);
                $soggetto->setDescrizioneDenominazione(NULL);
                $soggetto->setNumeroPartitaIva(NULL);
                $soggetto->setStato(NULL);
                $soggetto->setDomicilioFiscaleToponimo($personaFisica->domFiscIndirizzo);
                $soggetto->setDomicilioFiscaleComune($personaFisica->domFiscComune);
                $soggetto->setDomicilioFiscaleCap($personaFisica->domFiscCap);
                $soggetto->setDomicilioFiscaleProvincia($personaFisica->domFiscProvincia);

                $this->logger->info('getSoggetto : Passo Persona Fisica '.$cf.' Den: ', $soggetto->setDescrizioneDenominazione);
            }

            $soggetto->setDomicilioFiscaleStato('ITALIA');

            if($soggetto->getDomicilioFiscaleProvincia() == 'EE'){
                $soggetto->setDomicilioFiscaleStato($soggetto->getDomicilioFiscaleComune());
                $soggetto->setDomicilioFiscaleComune('');
            }

            $soggetto->setRappresentanteDatiIdentificativi($personaFisica->dittaIndivRapprFiscDenominazione);
            $soggetto->setRappresentanteCodiceFiscale($personaFisica->dittaIndivRapprFiscCodiceFiscale);
            $soggetto->setDescrizioneCarica($personaFisica->dittaIndivRapprDescCarica);

            //non usati nei form ma utili
            @$soggetto->setPartitaIvaMotivoCessazione($personaFisica->dittaIndivMotivoCessazione);
            @$soggetto->setConfluenzaPartitaIva($personaFisica->dittaIndivPivaConfluenza);
        } else {
            //società
            $soggetto->setTipo(\modelSoggetto::$tipoSocieta);
            $soggetto->setCodiceFiscale($soggettoDiverso->codiceFiscale);
            $soggetto->setVersione($soggettoDiverso->versione);
            $soggetto->setDescrizioneDenominazione($soggettoDiverso->denominazione);

            if($soggettoDiverso->partitaIva=="-")
              $soggetto->setNumeroPartitaIva(NULL);
            else
                $soggetto->setNumeroPartitaIva($soggettoDiverso->partitaIva);

            $soggetto->setStato($soggettoDiverso->statoPiva);

            $this->logger->info('getSoggetto: Passo Ditta '.$cf.' Den: ', $soggetto->setDescrizioneDenominazione);

            $dataFine = $soggettoDiverso->dataFinePiva;

            if(!empty($dataFine)){
                $dataSplittata = explode('/', $dataFine);
                $soggetto->setDataFineYear($dataSplittata[2]);
                $soggetto->setDataFineMonth($dataSplittata[1]);
                $soggetto->setDataFineDay($dataSplittata[0]);
            }

            $soggetto->setSedeLegaleToponimo($soggettoDiverso->sedeLegaleIndirizzo);
            $soggetto->setSedeLegaleComune($soggettoDiverso->sedeLegaleComune);
            $soggetto->setSedeLegaleCap($soggettoDiverso->sedeLegaleCap);
            $soggetto->setSedeLegaleProvincia($soggettoDiverso->sedeLegaleProvincia);
            $soggetto->setSedeLegaleStato('ITALIA');

            if($soggetto->getDomicilioFiscaleProvincia() == 'EE'){
                $soggetto->setSedeLegaleStato($soggetto->getSedeLegaleComune());
                $soggetto->setSedeLegaleComune('');
            }

            $soggetto->setDomicilioFiscaleToponimo($soggettoDiverso->domFiscIndirizzo);
            $soggetto->setDomicilioFiscaleComune($soggettoDiverso->domFiscComune);
            $soggetto->setDomicilioFiscaleCap($soggettoDiverso->domFiscCap);
            $soggetto->setDomicilioFiscaleProvincia($soggettoDiverso->domFiscProvincia);
            $soggetto->setDomicilioFiscaleStato('ITALIA');

            if($soggetto->getDomicilioFiscaleProvincia() == 'EE'){
                $soggetto->setDomicilioFiscaleStato($soggetto->setDomicilioFiscaleProvincia(''));
                $soggetto->setDomicilioFiscaleComune('');
            }

            $soggetto->setRappresentanteDatiIdentificativi($soggettoDiverso->rapprDenominazione);
            $soggetto->setRappresentanteCodiceFiscale($soggettoDiverso->rapprCodiceFiscale);
            $soggetto->setDescrizioneCarica($soggettoDiverso->rapprDescCarica);
            $soggetto->setComuneDiNascita($soggettoDiverso->rapprLuogoNascita);
            $soggetto->setProvinciaDiNascita($soggettoDiverso->rapprProvNascita);
            $soggetto->setStatoDiNascita('ITALIA');

            if($soggetto->getProvinciaDiNascita() == 'EE'){
                $soggetto->setStatoDiNascita($soggetto->getComuneDiNascita());
                $soggetto->setComuneDiNascita('');
            }
            $soggetto->setDataDiNascita($soggettoDiverso->rapprDataNascita);

            // non usati nei form ma utili
            $soggetto->setPartitaIvaMotivoCessazione($soggettoDiverso->motivoCessazione);
            $soggetto->setConfluenzaPartitaIva($soggettoDiverso->partitaIvaConfluenza);
        }

        $this->logger->info('getSoggetto:  TERMINE CREAZIONE OGGETTO ' . $cf);

        /* dati di output */
        if(is_null($soggetto)){
            $this->logger->warning('getSoggetto: '.$cf.' - Soggetto non presente in Anagrafe Tributaria');
            throw new Exception('Soggetto non presente in anagrafe tributaria');
        }

        return $this->objectToArray($soggetto);
    }

    private function objectToArray($object)
    {
        $array = [];

        if (!is_object($object) && !is_array($object)) {
            return $object;
        }

        if (is_object($object)) {
            $keys = array_keys((array)$object);

            $r = new \ReflectionObject($object);

            foreach($keys as $val) {
                $p = $r->getProperty($val);
                $p->setAccessible(true);

                $v = str_replace('modelSoggetto', '', $val);
                $v = trim($v);

                $array[$v] = $p->getValue($object);
            }

            $array = array_filter($array);
        }

        return $array;
    }
}

?>