<?php
/**
 * La seguente classe realizza il rendering di un file CSV.
 *
 * PHP version 7.x | PHPSlim 3.x
 *
 * @package CSVManager
 * @author  Original Author <f.santini@almaviva.it>
 * @version 2.0 2019-10-15
 */

class CSVManager
{
    protected $logger;

    public function __construct(\Monolog\Logger $logger)
    {
        global $appConfig;

        $this->logger = $logger;
        $this->config = $appConfig;
    }

    /**
     * 
     * @param array  "datasetList" Resultset procedura.
     * @param string "fileName"    Nome del file da esporre.
     * @param array  "nomiColonna" Intestazioni delle colonne.
	 * 
     */

	public function renderCSV($docData)
    {
        if ($this->config['debug'] === true) {
            $this->logger->info('CSVManager: docData', $docData);
        }

        // Percorso del file da creare.
        $csvFilename = '../../tmp/'.$docData['fileName'];
        $fileHandle  = fopen($csvFilename, 'a');

        // Inserimento delle colonne di intestazione.
        $columns = $docData['nomiColonna'];
        if ($fileHandle) {
            fputcsv($fileHandle, $columns, ';');
        }

        // Inserimento dei dei dati.
        foreach($docData['datasetList'] as $k => $v) {
            if ($fileHandle) {
                fputcsv($fileHandle, $v, ';');
            }
        }

        $document = file_get_contents($csvFilename);

        // Cancellazione del file.
        @unlink($csvFilename);

        return $document;
	}
}

?>