<?php
/**
 * JSON-RPC client.
 *
 * PHP version 4.
 *
 * @package JsonRpcClient
 * @author  Massimo Squillace <msquillace@sogei.it>
 * @version Release: 1.0 2009-11-16
 */

/**
 * JsonRpcClient permette il richiamo via JSON-RPC di metodi o funzioni.
 *
 * La classe invoca il servizio remoto via JSON-RPC e ritorna la risposta
 * o la coppia codice_di_errore / messaggio_di_errore.
 *
 * Requisiti:
 *     PHP 4 e superiore.
 */
class JsonRpcClient
{
    /**
     * HTTP wrapper object.
     * @var object
     */
    var $httpWrap;

    /**
     * URL da richiamare.
     * @var string
     */
    var $url;

    /**
     * Il costruttore riceve in input un array associativo, che puo` contenere:
     *
     * $options['url']
     *         La URL del programma remoto che definisce le interfacce JSON-RPC
     *         esposte. Puo` specificare lo schema "http://" oppure "https://",
     *         ma in questa versione e` supportato il solo SSL sbilanciato.
     *
     * $options['httpWrapper']
     *         Parametro opzionale, puo` essere impostato ad 'HttpWrapper_CURL'
     *         oppure ad 'HttpWrapper_PHP' per forzare l'utilizzo del CURL o
     *         della fsockopen(). Per default, se e` configurata l'estensione
     *         CURL la classe la utilizza, sfruttando la fsockopen() negli
     *         altri casi.
     *
     * $options['errlog']
     *         Se impostato ad un nome file, la classe vi registra tutti
     *         i messaggi di errore. Opzionale.
     *
     * @param array $options Array associativo di opzioni.
     */
    function JsonRpcClient($options) {
        if (!((substr(strtolower($options['url']), 0, 7) == "http://") ||
            (substr(strtolower($options['url']), 0, 8) == "https://")) ) {
            $this->url = null;
        } else {
            $this->url = $options['url'];
        }

        if (!@empty($options['httpWrapper'])) {
            switch($options['httpWrapper']) {
                case 'HttpWrapper_CURL':
                    $this->httpWrap = new HttpWrapper_CURL();
                    break;
                case 'HttpWrapper_PHP':
                    $this->httpWrap = new HttpWrapper_PHP();
                    break;
                default:
                    $wr = new HttpWrapper();
                    $this->httpWrap = $wr->GetWrapper();
                    //$this->httpWrap = HttpWrapper::GetWrapper(); // PHP 5
            }
        } else {
            $wr = new HttpWrapper();
            $this->httpWrap = $wr->GetWrapper();
            //$this->httpWrap = HttpWrapper::GetWrapper(); // PHP 5
        }

        $this->errLog = @empty($options['errlog']) ? null : $options['errlog'];
    }

    /**
     * Il metodo riceve in input un array associativo, che puo` contenere:
     *
     * $options['service']
     *         Il nome del servizio remoto da invocare.
     *
     * $options['params']
     *         Un array, tipicamente associativo, contenente i parametri da
     *         passare al servizio (metodo / funzione) remoto. Opzionale.
     *
     * Il metodo restituisce al chiamante un array associativo contenente gli
     * elementi:
     *
     * 'retcode' : codice di ritorno numerico, che vale 0 se la chiamata
     *             JSON-RPC si e` conclusa con successo;
     *
     * 'info'    : messaggio informativo relativo a 'retcode'.
     *
     * 'result'  : il risultato della elaborazione del servizio remoto. E'
     *             un array associativo contenente i campi 'data' ed 'error'.
     *             Il campo 'data' e` impostato a NULL se il servizio remoto
     *             e` terminato con un errore, altrimenti contiene le informazioni
     *             richieste, nel formato stabilito dal servizio. Il campo 'error'
     *             e` invece impostato a NULL se l'elaborazione del servizio
     *             remoto si e` conclusa con successo, altrimenti contiene un
     *             messaggio diagnostico.
     *
     * @param array $options Array associativo di opzioni.
     */
    function call_service($options) {
        if (@empty($options['service'])) {
            return $this->rpc_wlog(100, 'Nome servizio non specificato');
        }
        if (@empty($options['params'])) {
            $options['params'] = array();
        }

        $postData = array ("id"=>"JsonRpcClient",
                           "method"=>$options['service'],
                           "params"=>$options['params']);
        $postData = json_encode($postData); // Associative arrays always encoded as objects

        $jsonresult = $this->httpWrap->post($this->url, $postData);

        $result = json_decode($jsonresult);
        if ((is_object($result)) && ($result->id == "JsonRpcClient")) {
            return array('retcode' => 0, 'result' => array('data' => $result->result,
                        'error' => $result->error), 'info' => 'Chiamata JSON-RPC OK');
        } else {
            return $this->rpc_wlog(101, 'Chiamata JSON-RPC fallita');
        }
    }

    function rpc_wlog($code, $msg) {
        if (@!empty($this->errLog)) {
            $me  = &$_SERVER['SCRIPT_NAME'];
            $u   = &$_SERVER['REMOTE_ADDR'];
            $pid = sprintf('%-6s', getmypid());
            @error_log(date('d/m/Y H:i:s')." PID:$pid $msg [$code] -> $u $me\n", 3, $this->errLog.'.'.date('Ymd'));
        }
        return array('retcode' => $code, 'result' => null, 'info' => $msg);
    }
}

class HttpWrapper_CURL {
    function post($URL, $data, $referrer = '') {
        // parsing the given URL
        $URL_Info = parse_url($URL);
        if (!isset($URL_Info['port'])) {
            $URL_Info['port'] = (strtolower($URL_Info['scheme']) == 'https') ? 443 : 80;
        }

        // Building referrer
        if (empty($referrer)) { // if not given use this script as referrer
            $referrer=$_SERVER['SCRIPT_NAME'];
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $URL_Info['scheme'].'://'.$URL_Info['host'].$URL_Info['path']);
        curl_setopt($ch, CURLOPT_PORT, $URL_Info['port']);  // Set the correct port no.
        curl_setopt($ch, CURLOPT_HEADER, 0);                // Don't return headers
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        // Return the result when curl_exec is called
        curl_setopt($ch, CURLOPT_REFERER, $referrer);       // The referrer
        curl_setopt($ch, CURLOPT_POST, 0);                  // We're doing a post call
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);        // Here's the post data

        $result = curl_exec ($ch);

        curl_close($ch);
        return $result;
    }
}

class HttpWrapper_PHP {
    function post($URL, $data, $referrer = '') {
        // parsing the given URL
        $URL_Info = parse_url($URL);
        if (!isset($URL_Info['port'])) {
            $URL_Info['port'] = (strtolower($URL_Info['scheme']) == 'https') ? 443 : 80;
        }

        // Building referrer
        if (empty($referrer)) { // if not given use this script as referrer
            $referrer=$_SERVER['SCRIPT_NAME'];
        }

        // Need ssl:// prefix to hostname?
        if (strtolower($URL_Info['scheme']) == 'https') {
            $host = 'ssl://'.$URL_Info['host'];
        } else {
            $host = $URL_Info['host'];
        }

        // building POST-request:
        $request  = "POST ".$URL_Info['path']." HTTP/1.0\n";
        $request .= "Host: ".$URL_Info['host']."\n";
        $request .= "Referer: $referrer\n";
        $request .= "Content-type: text/plain\n";
        $request .= "Content-length: ".strlen($data)."\n";
        $request .= "Connection: close\n";
        $request .= "\n";
        $request .= $data ."\n";

        $result = '';

        $fp = fsockopen($host, $URL_Info['port']);
        fputs($fp, $request);		
        // We don't capture the HTTP Header
        $bCapturing = false;
        while(!feof($fp)) {
            $curline = fgets($fp, 4096);
            if ($bCapturing) {
                $result .= $curline;
            } elseif (strlen(trim($curline)) == 0) {
                $bCapturing=true;
            }
        }
        fclose($fp);
        return $result;
    }
}

class HTTPWrapper {
    function GetWrapper() {
        if (extension_loaded('curl')) {
            return new HttpWrapper_CURL();
        } else {
            return new HttpWrapper_PHP();
        }
    }
}

if (!function_exists('json_decode')) {
    function json_decode($content, $assoc=false) {
        require_once '../include/JSON.php';
        //require_once dirname(__FILE__).'/JSON.php';
        if ($assoc) {
            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        }
        else {
            $json = new Services_JSON;
        }
        return $json->decode($content);
    }
}

if (!function_exists('json_encode')) {
    function json_encode($content) {
        require_once '../include/JSON.php';
        //require_once dirname(__FILE__).'/JSON.php';
        $json = new Services_JSON;
        if (method_exists($json, '_encode')) {
            return $json->_encode($content);
        } else {
            return $json->encode($content);
        }
    }
}
