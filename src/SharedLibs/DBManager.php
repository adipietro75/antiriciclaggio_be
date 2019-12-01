<?php
/**
 * La seguente classe realizza uno strato intermedio tra applicazione e database
 *
 * PHP version 7.x | PHPSlim 3.x
 *
 * @package DBManager
 * @author  Original Author <f.santini@almaviva.it>
 * @version 2.0 2019-10-15
 */

namespace NIM_Backend\SharedLibs;

class DBManager
{
	public $stmt;

	var $tipoOperazione;
	var $query;
	var $procedura;
	var $funzione;
	var $conn;
	var $clobIn;
	var $blobIn;
	var $clobOut;
	var $blobOut;
	var $numRighe;
	var $errore;
	var $nome;
	var $logger;
	var $dataForLog;

	var $cursor = [];

	var $parametriIn   = [];
	var $parametriOut  = [];
	var $collectionIn  = [];
	var $collectionOut = [];

	var $oLobInArray     = [];
	var $oLobOutArray    = [];
	var $oCollInArray    = [];
	var $oCollOutArray   = [];
	var $oCursorOutArray = [];

    public function __construct(\Monolog\Logger $logger)
    {
        $this->setLog($logger);
    }

    private function setConn($conn)
    {
        $this->conn = $conn;
    }

    public function getConn(){
        return @$this->conn;
    }

    private function setNome($nome)
    {
        $this->nome = $nome;
    }

    private function getNome()
    {
        return @$this->nome;
    }

    private function setNumRighe($nb)
    {
        $this->numRighe = $nb;
    }

    public function getNumRighe()
    {
        return @$this->numRighe;
    }

    private function setTipoOperazione($tipo)
    {
        $this->tipoOperazione = $tipo;
    }

    private function getTipoOperazione()
    {
        return strtoupper($this->tipoOperazione);
    }

    private function setStmt( $st = null )
    {
        if ($st == null) {
            $this->setErrore( "QUERY NON VALIDA -> " . $this->getNome() );
        }

        $this->stmt = $st;
    }

    private function getStmt()
    {
        return @$this->stmt;
    }

    public function setDataForLog()
    {
        $this->dataForLog['Query']       = $this->getNome();
        $this->dataForLog['parametriIn'] = $this->getParametriIn();
        $this->dataForLog['Risultato']   = $this->getParametriOut();
        $this->dataForLog['Errore']      = $this->getErrore();
    }

    public function getDataForLog()
    {
        return @$this->dataForLog;
    }

    public function setProcedura($nome)
    {
        $this->procedura = $nome;
        $this->setNome($nome);
        $this->putLog( "info", "Procedura -> [" . $nome . "]" );

        $this->setTipoOperazione('P');
    }

    private function getProcedura()
    {
        return @$this->procedura;
    }

    public function setFunzione($nome)
    {
        $this->funzione= $nome;
        $this->setNome($nome);
        $this->putLog( "info", "Funzione -> [" . $nome . "]" );

        $this->setTipoOperazione('F');
    }

    private function getFunzione()
    {
        return @$this->funzione;
    }

    public function setQuery($query)
    {
        $this->query = $query;
        $this->setNome($query);
        $this->putLog( "info", "Query -> [" . $query . "]" );

        $this->setTipoOperazione('Q');
    }

    public function getQuery()
    {
        return @$this->query;
    }

    public function setParametriIn($pin)
    {
        $this->parametriIn = $pin;
    }

    private function getParametriIn()
    {
        return @$this->parametriIn;
    }

    public function setParametriOut($pout)
    {
        $this->parametriOut = $pout;
    }

    private function getParametriOut()
    {
        return @$this->parametriOut;
    }

    public function setCollectionIn( $collIn = [] )
    {
        $this->collectionIn = $collIn;
    }

    private function getCollectionIn()
    {
        return @$this->collectionIn;
    }

    public function setCollectionOut( $collOut = [] )
    {
        $this->collectionOut = $collOut;
    }

    private function getCollectionOut()
    {
        return @$this->collectionOut;
    }

    public function setClobIn($clobin)
    {
        $this->clobIn = $clobin;
    }

    private function getClobIn()
    {
        return @$this->clobIn;
    }

    public function setBlobIn($blobin)
    {
        $this->blobIn = $blobin;
    }

    private function getBlobIn()
    {
        return @$this->blobIn;
    }

    public function setClobOut($clobout)
    {
        $this->clobOut = $clobout;
    }

    private function getClobOut()
    {
        return @$this->clobOut;
    }

    public function setBlobOut($blobout)
    {
        $this->blobOut = $blobout;
    }

    private function getBlobOut()
    {
        return @$this->blobOut;
    }

    private function setOcollInArray( $array = [] )
    {
        $this->oCollInArray = $array;
    }

    private function getOcollInArray()
    {
        return @$this->oCollInArray;
    }

    private function setOlobInArray( $array = [] )
    {
        $this->oLobInArray = $array;
    }

    private function getOlobInArray()
    {
        return @$this->oLobInArray;
    }

    private function setOlobOutArray( $array = [] )
    {
        $this->oLobOutArray = $array;
    }

    private function getOlobOutArray()
    {
        return @$this->oLobOutArray;
    }

    private function setOcollOutArray( $array = [] )
    {
        $this->oCollOutArray = $array;
    }

    private function getOcollOutArray()
    {
        return @$this->oCollOutArray;
    }

    private function setOcursorOutArray( $array = [] )
    {
        $this->oCursorOutArray = $array;
    }

    private function getOcursorOutArray()
    {
        return @$this->oCursorOutArray;
    }


    private function setErrore($errore = null)
    {
        if ( $errore != null ) {
            $this->putLog( "error", $errore);
        }

        $this->errore = $errore;
    }

    private function getErrore()
    {
        return @$this->errore;
    }

    public function setCursor( $cursor = null )
    {
        $this->cursor = $cursor;
    }

    private function getCursor()
    {
        return @$this->cursor;
    }

    public function setLog($logger)
    {
        $this->logger = $logger;
    }

    private function getLog()
    {
        return @$this->logger;
    }

    private function putLog($level, $msg)
    {
        if (is_object(@$this->logger))
        {
            $this->logger->$level($msg);
        }
    }

    /************************************/

    public function rollback()
    {
        $conn = $this->getConn();

        if (!oci_rollback($conn))
        {
            $this->putLog( "error", "Errore nella procedura di RollBack" );
        } else {
            $this->putLog( "info", "Procedura di RollBack eseguita con Successo" );
        }
    }

    public function commit()
    {
        $conn = $this->getConn();

        if (!oci_commit($conn))
        {
            $this->putLog( "error", "Errore nella procedura di Commit");
        } else {
            $this->putLog( "info", "Procedura di Commit eseguita con Successo");
        }
    }

    // Distruzione Oggetti Cursor e xLOB
    private function cleanLobCursor()
    {
        if ($this->getOlobInArray())
        {
            foreach($this->getOlobInArray() as $key => $value)
            {
                if( !$value->close())
                {
                    $this->setErrore( "Errore chiusura CLOB in [" . $key . "]" );
                }

                if (!$value->free())
                {
                    $this->setErrore( "Errore Free CLOB in [" . $key . "]" );
                }
            }

            $this->setOlobInArray();
        }

        if ($this->getOcollInArray())
        {
            foreach($this->getOcollInArray() as $key => $value)
            {
                if (!$value->free())
                {
                    $this->setErrore( "Errore Free Coll in [" . $key . "]" );
                }
            }

            $this->setOcollInArray();
        }

        if ($this->getOlobOutArray())
        {
            foreach($this->getOlobOutArray() as $key => $value)
            {
                if (!$value->free())
                {
                    $this->setErrore( "Errore Free CLOB Out [" . $key . "]" );
                }
            }

            $this->setOlobOutArray();
        }

        if ($this->getOcursorOutArray())
        {
            foreach($this->getOcursorOutArray() as $key => $value)
            {
                if (!ocifreecursor($value))
                {
                    $this->setErrore( "Pulizia Cursore non andata a buon fine [" . $key . "]" );

                }

            }

            $this->setOcursorOutArray();
        }

    }

    // Setta i parametri d'ingresso e d'uscita dell'oggetto Oracle da chiamare
    public function setParametri( $parametriIn = null, $clobIn = null, $blobIn = null, $parametriOut = null, $clobOut = null, $blobOut = null)
    {
        $this->setParametriIn($parametriIn);
        $this->setParametriOut($parametriOut);
        $this->setClobIn($clobIn);
        $this->setBlobIn($blobIn);
        $this->setClobOut($clobOut);
        $this->setBlobOut($blobOut);
    }

    // Fa il bind dei parametri d'ingresso
    private function bindIn()
    {
        $conn = $this->getConn();

        if (is_array($this->getParametriIn()))
        {
            $lobInArray  = $this->getOlobInArray();
            $collInArray = $this->getOcollInArray();

            $params = $this->getParametriIn();

            foreach($params as $key => $value)
            {
                if (is_array($this->getClobIn()) && in_array($key, $this->getClobIn()))
                {
                    // bind clobIn
                    $clob1 = oci_new_descriptor($conn, OCI_D_LOB);

                    $lobInArray[$key] = $clob1;

                    if (!oci_bind_by_name($this->getStmt(), ":".trim($key), $clob1, -1, OCI_B_CLOB))
                    {
                        $this->setErrore(oci_error($this->getStmt()));
                        return;
                    }

                    $clob1->WriteTemporary($params[$key]);
                } else if (is_array($this->getBlobIn()) && in_array($key, $this->getBlobIn()))
                {
                    // bind blobIn
                    $clob1 = oci_new_descriptor($conn, OCI_D_LOB);

                    $lobInArray[$key] = $clob1;

                    if (!oci_bind_by_name($this->getStmt(), ":".trim($key), $clob1, -1, OCI_B_BLOB))
                    {
                        $this->setErrore(oci_error($this->getStmt()));
                        return;
                    }

                    $clob1->WriteTemporary($params[$key], OCI_TEMP_BLOB);
                } else if (is_array($this->getCollectionIn()) && array_key_exists($key, $this->getCollectionIn()))
                {
                    $tmp  = $this->getCollectionIn();
                    $type = $tmp[$key];

                    $Categories = oci_new_collection($conn, $type);

                    if (!$Categories)
                    {
                        $this->setErrore(oci_error($this->getStmt()));
                        return;
                    }

                    if ($params[$key])
                    {
                        foreach($params[$key] as $k => $val)
                        {
                            $Categories->append($val);
                        }
                    }

                    $collInArray[$key] = $Categories;

                    if (!oci_bind_by_name($this->getStmt(), ":".trim($key), $Categories, -1, OCI_B_NTY))
                    {
                        $this->setErrore(oci_error($this->getStmt()));
                        return;
                    }
                } else {
                    // bind altri tipi di parametri
                    if (!oci_bind_by_name($this->getStmt(), ":".trim($key), $params[$key], -1))
                    {
                        $this->setErrore(oci_error($this->getStmt()));
                        return;
                    }
                }
            }

            $this->setOlobInArray($lobInArray);
            $this->setOcollInArray($collInArray);
        }

        return;
    }

    // Fa il bind dei parametri d'uscita
    private function bindOut()
    {
        $conn = $this->getConn();
        $pOut = null;

        if (is_array($this->getParametriOut()))
        {
            $oLobOutArray    = $this->getOlobOutArray();
            $oCursorOutArray = $this->getOcursorOutArray();
            $oCollOutArray   = $this->getOcollOutArray();

            $params = $this->getParametriOut();

            foreach ($params as $value)
            {
                if (is_array($this->getClobOut()) && in_array($value, $this->getClobOut()))
                {
                    // bind clobOut
                    $clob = oci_new_descriptor($conn, OCI_D_LOB);

                    $oLobOutArray[$value] = $clob;

                    if (!oci_bind_by_name($this->getStmt(), ":".trim($value), $clob, -1, OCI_B_CLOB))
                    {
                        $this->setErrore(oci_error($this->getStmt()));
                        return;
                    }
                } else if (is_array($this->getBlobOut()) && in_array($value, $this->getBlobOut())) {
                    // bind blobOut
                    $clob = oci_new_descriptor($conn, OCI_D_LOB);

                    $oLobOutArray[$value] = $clob;

                    if (!oci_bind_by_name($this->getStmt(), ":".trim($value), $clob, -1, OCI_B_BLOB))
                    {
                        $this->setErrore(oci_error($this->getStmt()));
                        return;
                    }
                } else if (is_array($this->getCursor()) && in_array($value, $this->getCursor())) {
                    // bind cursor
                    $curs = oci_new_cursor($conn);

                    $oCursorOutArray[$value] = $curs;

                    if (!oci_bind_by_name($this->getStmt(), ":".trim($value), $curs, -1, OCI_B_CURSOR))
                    {
                        $this->setErrore(oci_error($this->getStmt()));
                        return;
                    }
                } else if (is_array($this->getCollectionOut()) && array_key_exists($value, $this->getCollectionOut())) {
                    // bind collection
                    $tmp  = $this->getCollectionOut();
                    $type = $tmp[$value];

                    $Categories = oci_new_collection($conn, $type);

                    if (!$Categories)
                    {
                        $this->setErrore(oci_error($this->getStmt()));
                        return;
                    }

                    $oCollOutArray[$value] = $Categories;

                    if (!oci_bind_by_name($this->getStmt(), ":".trim($value),   $Categories, -1, OCI_B_NTY))
                    {
                        $this->setErrore(oci_error($this->getStmt()));
                        return;
                    }
                } else {
                    // bind altri tipi di parametri
                    if (!oci_bind_by_name($this->getStmt(), ":".trim($value), $pOut[$value], 50000))
                    {
                        $this->setErrore(oci_error($this->getStmt()));
                        return;
                    }

                    // setta i parametri d'uscita
                    $this->setParametriOut($pOut);
                }
            }

            $this->setOlobOutArray($oLobOutArray);
            $this->setOcursorOutArray($oCursorOutArray);
            $this->setOcollOutArray($oCollOutArray);
        }

        return;
    }

    // Esegue la query
    private function eseguiQuery($viewResult)
    {
        $conn = $this->getConn();

        $this->setStmt(oci_parse($conn, $this->getQuery()));

        if ($this->getErrore())
        {
            return;
        }

        $this->bindIn();
        $this->bindOut();

        if ($this->getErrore())
        {
            return;
        }

        $exr = @oci_execute($this->getStmt(), OCI_NO_AUTO_COMMIT);

        if (!$exr)
        {
            $this->setErrore(oci_error($this->getStmt()));
            return;
        }

        if (!$this->getErrore())
        {
            $posI = strpos(trim(strtolower($this->getQuery())), 'insert');
            $posU = strpos(trim(strtolower($this->getQuery())), 'update');

            if($posI === 0 || $posU === 0)
            {
                $this->setNumRighe(@oci_num_rows($this->getStmt()));
            } else {
                $this->setNumRighe(@oci_fetch_all($this->getStmt(), $results, null, null, constant($viewResult)));

                if ($this->getNumRighe() > 0)
                {
                    $pOut = $results;

                    // setta i parametri d'uscita
                    $this->setParametriOut($pOut);
                }
            }
        }
    }

    // Esegue la procedura
    private function eseguiProcedura($viewResult)
    {
        $conn = $this->getConn();

        $strParam = null;

        if (is_array($this->getParametriIn()))
        {
            foreach($this->getParametriIn() as $key => $value)
            {
                $strParam .= ":".trim($key).", ";
            }
        }

        if (is_array($this->getParametriOut()))
        {
            foreach($this->getParametriOut() as $key => $value)
            {
                $strParam .= ":".trim($value).", ";
            }
        }

        $strParam = substr($strParam, 0, strlen($strParam) - 2);

        $sql = "BEGIN " . $this->getProcedura() . "(" . trim($strParam) . "); END;";

        $this->setStmt(oci_parse($conn, $sql));

        if ($this->getErrore())
        {
            return;
        }

        $this->bindIn();
        $this->bindOut();

        if ($this->getErrore())
        {
            return;
        }

        $exr = oci_execute($this->getStmt(), OCI_NO_AUTO_COMMIT);

        if (!$exr)
        {
            $this->setErrore(oci_error($this->getStmt()));
            return;
        }

        if (!$this->getErrore())
        {
            if ($this->getOlobOutArray())
            {
                // Associa il xLOB della risposta all'array $parametriOut
                foreach ($this->getOlobOutArray() as $key => $value)
                {
                    if ($value)
                    {
                        $pOut = $this->getParametriOut();
                        $pOut[$key] = @$value->load();
                        $this->setParametriOut($pOut);

                    }
                }
            }

            if ($this->getOcursorOutArray())
            {
                // Associa il Cursore della risposta all'array $parametriOut
                foreach ($this->getOcursorOutArray() as $ke => $cur)
                {
                    if (false === @oci_execute($cur))
                    {
                        $this->setErrore(oci_error($this->getStmt()));
                        continue;
                    }

                    $this->setNumRighe(@oci_fetch_all($cur, $results, null, null, constant($viewResult)));
                    $pOut = $this->getParametriOut();
                    $pOut[$ke] = $results;
                    $this->setParametriOut($pOut);
                }
            }

            if ($this->getOcollInArray())
            {
                // Associa la Colection della risposta all'array $parametriOut
                foreach ($this->getOcollOutArray() as $ke => $coll)
                {
                    $pOut   = $this->getParametriOut();
                    $tmp    = array();

                    if ($coll->size() != 0)
                    {
                        for ($x=0; $x < $coll->size(); $x++)
                        {
                            array_push($tmp, $coll->getElem($x));
                        }

                        $pOut[$ke] = $tmp;
                    } else {
                        $pOut[$ke] = null;
                    }

                    // setta i parametri d'uscita
                    $this->setParametriOut($pOut);
                }
            }
        }
    }

    // Esegue una Funzione
    private function eseguiFunzione($viewResult)
    {
        $conn = $this->getConn();

        $strParam = '';

        if (is_array($this->getParametriIn()))
        {
            foreach ($this->getParametriIn() as $key => $value)
            {
                $strParam .= ":$key, ";
            }
        }

        if (is_array($this->getParametriOut()))
        {
            foreach ($this->getParametriOut() as $key => $value)
            {
                $strParam .= ":$key, ";
            }
        }

        $strParam = substr($strParam,0,strlen($strParam) - 2);

        $sql = "begin :ris := " . $this->getFunzione() . " ( " . $strParam . "); end;";

        $this->setStmt(oci_parse($conn, $sql));

        if ($this->getErrore())
        {
            return;
        }

        $this->bindIn();
        $this->bindOut();

        if ($this->getErrore())
        {
            return;
        }

        // fa il bind della variabile di ritorno della funzione
        $pOut = $this->getParametriOut();

        if ($this->getErrore())
        {
            return;
        }

        oci_bind_by_name($this->getStmt(), ":ris", $pOut['risultato'], 10000);
        $this->setParametriOut($pOut);

        $exr = oci_execute($this->getStmt(), OCI_NO_AUTO_COMMIT);

        if (!$exr)
        {
            $this->setErrore(oci_error($this->getStmt()));
            return;
        }

        if (!$this->getErrore())
        {
            if ($this->getOlobOutArray())
            {
                // Associa il xLOB della risposta all'array $parametriOut
                foreach ($this->getOlobOutArray() as $key => $value)
                {
                    if ($value)
                    {
                        $pOut = $this->getParametriOut();
                        $pOut[$key] = $value->load();
                        $this->setParametriOut($pOut);

                    }
                }
            }

            if ($this->getOcursorOutArray())
            {
                // Associa il Cursore della risposta all'array $parametriOut
                foreach($this->getOcursorOutArray() as $ke => $cur)
                {
                    if (false === @oci_execute($cur))
                    {
                        $this->setErrore(oci_error($this->getStmt()));
                        continue;
                    }

                    $this->setNumRighe(@oci_fetch_all($cur, $results, null, null, constant($viewResult)));

                    $pOut = $this->getParametriOut();
                    $pOut[$ke] = $results;
                    $this->setParametriOut($pOut);
                }
            }
        }
    }

    // Esegue la richiesta
    public function esegui( $resType = 'ROW' ) // OCI_FETCHSTATEMENT_BY_COLUMN || OCI_FETCHSTATEMENT_BY_ROW
    {
        $viewResult = "OCI_FETCHSTATEMENT_BY_".$resType;

        $this->setErrore(null);

        // 'P' store procedure 'F' private function 'Q' query
        switch($this->getTipoOperazione())
        {
            case 'P':
                $this->eseguiProcedura($viewResult);
                break;
            case 'F':
                $this->eseguiFunzione($viewResult);
                break;
            case 'Q':
                $this->eseguiQuery($viewResult);
                break;
            default:
                return false;
        }

        $this->setDataForLog();

        // setta i parametri d'uscita
        $out = $this->getParametriOut();

        // Azzero tutto
        $this->cleanLobCursor();
        $this->setParametri();
        $this->setCursor();
        $this->setCollectionOut();
        $this->setCollectionIn();

        oci_free_statement($this->getStmt());

        return $out;
    }

    // Inizializza la connessione
    public function initConnection($alias) {
        $dbstring = "(DESCRIPTION=(ADDRESS_LIST=(ADDRESS=(PROTOCOL=TCP)(HOST = 10.50.5.48)(PORT = 1630))(ADDRESS=(PROTOCOL=TCP)(HOST = 26.2.63.54)(PORT = 1630))(ADDRESS=(PROTOCOL=TCP)(HOST = 26.2.60.57)(PORT = 1521)))(SOURCE_ROUTE=yes)(CONNECT_DATA=(SERVICE_NAME = U12S)(SERVER=DEDICATED)))";

        if ($alias == "VLT_AMM" || $alias == "VLT") {
            $user = "VLT";
            $pass = "VLT";
        } else if ($alias == "C6WEB" || $alias == "C6") {
            $user = "COMMA6";
            $pass = "COMMA6";
        } else if ($alias == "PVCONC") {
            $user = "PVCONC";
            $pass = "PVCONC";
        } else if ($alias == "GP") {
            $user = "GP";
            $pass = "GP";
        }

        $conn = OCILogon($user, $pass, $dbstring, null, null);

        // require_once('/opt/web/php/include/cco_oracle.inc');
        // require_once('/opt/web/php/include/ociconnect.inc');

        // $res = oci_get_db_conn($alias, $conn, true);

        // if ($res == 0)
        if ($conn)
        {
            $this->setConn($conn);
            return true;
        } else {
            $this->setErrore('Non è stato possibile stabilire una connessione al DB');
            return false;
        }
    }

    // Chiude la connessione
    public function closeConnection()
    {
        $conn = $this->getConn();
        return oci_close($conn);
    }
}

?>