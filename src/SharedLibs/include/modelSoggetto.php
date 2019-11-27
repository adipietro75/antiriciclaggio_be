<?php

class modelSoggetto {

	public static $tipoPersonaFisica = '0';
	public static $tipoDittaIndividuale = '1';
	public static $tipoSocieta = '2';

	private $codiceFiscale;
	private $versione;
	private $descrizioneDenominazione;
	private $numeroPartitaIva;
	private $stato;
	private $dataFineYear;
	private $dataFineMonth;
	private $dataFineDay;
	private $fax;
	private $telefono;
	private $pec;
	private $pecRappresentante;
	private $dataValidazione;
	private $sesso;
	private $cognome;
	private $nome;
	private $comuneDiNascita;
	private $provinciaDiNascita;
	private $statoDiNascita;
	private $dataDiNascita;
	private $domicilioFiscaleToponimo;
	private $domicilioFiscaleComune;
	private $domicilioFiscaleCap;
	private $domicilioFiscaleProvincia;
	private $domicilioFiscaleStato;
	private $sedeLegaleToponimo;
	private $sedeLegaleComune;
	private $sedeLegaleCap;
	private $sedeLegaleProvincia;
	private $sedeLegaleStato;
	private $rappresentanteDatiIdentificativi;
	private $rappresentanteCodiceFiscale;
	private $descrizioneCarica;
	private $partitaIvaMotivoCessazione;
	private $partitaIvaDataInizio;
	private $partitaIvaDataFine;
	private $confluenzaPartitaIva;
	private $tipo;

	function getCodiceFiscale() {
		return $this->codiceFiscale;
	}

	function getDescrizioneDenominazione() {
		return $this->descrizioneDenominazione;
	}

	function getNumeroPartitaIva() {
		return $this->numeroPartitaIva;
	}

	function getStato() {
		return $this->stato;
	}

	function getDataFineYear() {
		return $this->dataFineYear;
	}

	function getDataFineMonth() {
		return $this->dataFineMonth;
	}

	function getDataFineDay() {
		return $this->dataFineDay;
	}

	function getFax() {
		return $this->fax;
	}

	function getTelefono() {
		return $this->telefono;
	}

	function getPec() {
		return $this->pec;
	}

	function getPecRappresentante() {
		return $this->pecRappresentante;
	}

	function getDataValidazione() {
		return $this->dataValidazione;
	}

	function getCognome() {
		return $this->cognome;
	}

	function getNome() {
		return $this->nome;
	}

	function getComuneDiNascita() {
		return $this->comuneDiNascita;
	}

	function getProvinciaDiNascita() {
		return $this->provinciaDiNascita;
	}

	function getDataDiNascita() {
		return $this->dataDiNascita;
	}

	function getDomicilioFiscaleToponimo() {
		return $this->domicilioFiscaleToponimo;
	}

	function getDomicilioFiscaleComune() {
		return $this->domicilioFiscaleComune;
	}

	function getDomicilioFiscaleCap() {
		return $this->domicilioFiscaleCap;
	}

	function getDomicilioFiscaleProvincia() {
		return $this->domicilioFiscaleProvincia;
	}

	function getSedeLegaleToponimo() {
		return $this->sedeLegaleToponimo;
	}

	function getSedeLegaleComune() {
		return $this->sedeLegaleComune;
	}

	function getSedeLegaleCap() {
		return $this->sedeLegaleCap;
	}

	function getSedeLegaleProvincia() {
		return $this->sedeLegaleProvincia;
	}

	function getRappresentanteDatiIdentificativi() {
		return $this->rappresentanteDatiIdentificativi;
	}

	function getRappresentanteCodiceFiscale() {
		return $this->rappresentanteCodiceFiscale;
	}

	function getDescrizioneCarica() {
		return $this->descrizioneCarica;
	}

	function getPartitaIvaMotivoCessazione() {
		return $this->partitaIvaMotivoCessazione;
	}

	function getPartitaIvaDataInizio() {
		return $this->partitaIvaDataInizio;
	}

	function getPartitaIvaDataFine() {
		return $this->partitaIvaDataFine;
	}

	function getConfluenzaPartitaIva() {
		return $this->confluenzaPartitaIva;
	}

	function setCodiceFiscale($codiceFiscale) {
		$this->codiceFiscale = $codiceFiscale;
	}

	function setDescrizioneDenominazione($descrizioneDenominazione) {
		$this->descrizioneDenominazione = $descrizioneDenominazione;
	}

	function setNumeroPartitaIva($numeroPartitaIva) {
		$this->numeroPartitaIva = $numeroPartitaIva;
	}

	function setStato($stato) {
		$this->stato = $stato;
	}

	function setDataFineYear($dataFineYear) {
		$this->dataFineYear = $dataFineYear;
	}

	function setDataFineMonth($dataFineMonth) {
		$this->dataFineMonth = $dataFineMonth;
	}

	function setDataFineDay($dataFineDay) {
		$this->dataFineDay = $dataFineDay;
	}

	function setFax($fax) {
		$this->fax = $fax;
	}

	function setTelefono($telefono) {
		$this->telefono = $telefono;
	}

	function setPec($pec) {
		$this->pec = $pec;
	}

	function setPecRappresentante($pecRappresentante) {
		$this->pecRappresentante = $pecRappresentante;
	}

	function setDataValidazione($dataValidazione) {
		$this->dataValidazione = $dataValidazione;
	}

	function setCognome($cognome) {
		$this->cognome = $cognome;
	}

	function setNome($nome) {
		$this->nome = $nome;
	}

	function setComuneDiNascita($comuneDiNascita) {
		$this->comuneDiNascita = $comuneDiNascita;
	}

	function setProvinciaDiNascita($provinciaDiNascita) {
		$this->provinciaDiNascita = $provinciaDiNascita;
	}

	function setDataDiNascita($dataDiNascita) {
		$this->dataDiNascita = $dataDiNascita;
	}

	function setDomicilioFiscaleToponimo($domicilioFiscaleToponimo) {
		$this->domicilioFiscaleToponimo = $domicilioFiscaleToponimo;
	}

	function setDomicilioFiscaleComune($domicilioFiscaleComune) {
		$this->domicilioFiscaleComune = $domicilioFiscaleComune;
	}

	function setDomicilioFiscaleCap($domicilioFiscaleCap) {
		$this->domicilioFiscaleCap = $domicilioFiscaleCap;
	}

	function setDomicilioFiscaleProvincia($domicilioFiscaleProvincia) {
		$this->domicilioFiscaleProvincia = $domicilioFiscaleProvincia;
	}

	function setSedeLegaleToponimo($sedeLegaleToponimo) {
		$this->sedeLegaleToponimo = $sedeLegaleToponimo;
	}

	function setSedeLegaleComune($sedeLegaleComune) {
		$this->sedeLegaleComune = $sedeLegaleComune;
	}

	function setSedeLegaleCap($sedeLegaleCap) {
		$this->sedeLegaleCap = $sedeLegaleCap;
	}

	function setSedeLegaleProvincia($sedeLegaleProvincia) {
		$this->sedeLegaleProvincia = $sedeLegaleProvincia;
	}

	function setRappresentanteDatiIdentificativi($rappresentanteDatiIdentificativi) {
		$this->rappresentanteDatiIdentificativi = $rappresentanteDatiIdentificativi;
	}

	function setRappresentanteCodiceFiscale($rappresentanteCodiceFiscale) {
		$this->rappresentanteCodiceFiscale = $rappresentanteCodiceFiscale;
	}

	function setDescrizioneCarica($descrizioneCarica) {
		$this->descrizioneCarica = $descrizioneCarica;
	}

	function setPartitaIvaMotivoCessazione($partitaIvaMotivoCessazione) {
		$this->partitaIvaMotivoCessazione = $partitaIvaMotivoCessazione;
	}

	function setPartitaIvaDataInizio($partitaIvaDataInizio) {
		$this->partitaIvaDataInizio = $partitaIvaDataInizio;
	}

	function setPartitaIvaDataFine($partitaIvaDataFine) {
		$this->partitaIvaDataFine = $partitaIvaDataFine;
	}

	function setConfluenzaPartitaIva($confluenzaPartitaIva) {
		$this->confluenzaPartitaIva = $confluenzaPartitaIva;
	}

	function getTipo() {
		return $this->tipo;
	}

	function setTipo($tipo) {
		$this->tipo = $tipo;
	}

	function getSesso() {
		return $this->sesso;
	}

	function setSesso($sesso) {
		$this->sesso = $sesso;
	}

	function getStatoDiNascita() {
		return $this->statoDiNascita;
	}

	function getDomicilioFiscaleStato() {
		return $this->domicilioFiscaleStato;
	}

	function setStatoDiNascita($statoDiNascita) {
		$this->statoDiNascita = $statoDiNascita;
	}

	function setDomicilioFiscaleStato($domicilioFiscaleStato) {
		$this->domicilioFiscaleStato = $domicilioFiscaleStato;
	}

	public function getSedeLegaleStato() {
		return $this->sedeLegaleStato;
	}

	public function setSedeLegaleStato($sedeLegaleStato) {
		$this->sedeLegaleStato = $sedeLegaleStato;
	}

	public function getVersione() {
		return $this->versione;
	}

	public function setVersione($versione) {
		$this->versione = $versione;
	}

		
	function isSocieta() {
		if ($this->tipo == self::$tipoSocieta) {
			return true;
		} else {
			return false;
		}
	}

	function isDittaIndividuale() {
		if ($this->tipo == self::$tipoDittaIndividuale) {
			return true;
		} else {
			return false;
		}
	}

	function isPersonaFisica() {
		if ($this->tipo == self::$tipoPersonaFisica) {
			return true;
		} else {
			return false;
		}
	}

	function isRappresentatoDaSocieta() {
		if (strlen($this->rappresentanteCodiceFiscale) == 11) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Torna i dati della persona fisica veramente rappresentante.
	 * In caso di scatole cinesi torna il rappresentante dell'ultima societa della catena.
	 * 
	 * $rappresentante['nome'];
	 * $rappresentante['cf'];
	 * @return boolean
	 */
	private $maxCountRicorsioneAt = 2;

	function getDatiRappresentanteLegaleFisico($count = 0) {
		if ($count == $this->maxCountRicorsioneAt) {
			return null;
		}

		if (strlen($this->rappresentanteCodiceFiscale) != 11) {
			$rappresentante['nome'] = $this->getRappresentanteDatiIdentificativi();
			$rappresentante['cf'] = $this->getRappresentanteCodiceFiscale();
			$rappresentante['descrizione'] = $this->getDescrizioneCarica();
			$rappresentante['dataNascita'] = $this->getDataDiNascita();
			$rappresentante['count'] = $count;
			return $rappresentante;
		} elseif (strlen($this->rappresentanteCodiceFiscale) == 11) {
			$at = new common_anagrafetributaria_AtManager();
			$nuovoSoggetto = $at->getSoggetto($this->rappresentanteCodiceFiscale);
			return $nuovoSoggetto->getDatiRappresentanteLegaleFisico($count + 1);
		}
	}

	function isRappresentanteLegale($cf, $count = 0) {
		if ($count == $this->maxCountRicorsioneAt) {
			return null;
		}
		if (strlen($this->rappresentanteCodiceFiscale) != 11) {

			if ($this->isPersonaFisica()) {
				if (trim(strtoupper($cf)) == $this->getCodiceFiscale()) {
					return true;
				} else {
					return false;
				}
			}
			if ($this->isDittaIndividuale()) {
				$rappr_at = $this->rappresentanteCodiceFiscale;
				if (!empty($rappr_at)) {
					if (trim(strtoupper($cf)) == $rappr_at) {
						return true;
					} else {
						return false;
					}
				} else {
					if (trim(strtoupper($cf)) == $this->getCodiceFiscale()) {
						return true;
					} else {
						return false;
					}
				}
			}
			if ($this->isSocieta()) {
				$rappr_at = $this->rappresentanteCodiceFiscale;
				if (!empty($rappr_at)) {
					if (trim(strtoupper($cf)) == $rappr_at) {
						return true;
					} else {
						return false;
					}
				} else {
					if (trim(strtoupper($cf)) == $this->getCodiceFiscale()) {
						return true;
					} else {
						return false;
					}
				}
			}
		}

		// Se il rappr. legale e' una societa'
		if (strlen($this->rappresentanteCodiceFiscale) == 11) {
			$at = new common_anagrafetributaria_AtManager();
			$nuovoSoggetto = $at->getSoggetto($this->rappresentanteCodiceFiscale);
			return $nuovoSoggetto->isRappresentanteLegale($cf, $count + 1);
		}
	}

}
?>