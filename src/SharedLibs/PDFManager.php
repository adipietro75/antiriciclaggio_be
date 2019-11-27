<?php
/**
 * La seguente classe realizza il rendering di un file PDF.
 *
 * PHP version 7.x | PHPSlim 3.x
 *
 * @package PDFManager
 * @author  Original Author <f.santini@almaviva.it>
 * @version 2.0 2019-10-15
 */

class PDFManager
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
	 * @param array|string "templateName"   array di stringhe o stringa con il nome del file twig
	 * @param string       "templatePath"   path assoluto del folder che contiene i file twig usati come templates
	 * @param array        "templateParams" array di parametri da passare al twig da renderizzare
	 * @param string       "templateHash"   codice indentificativo del file
	 * 
	 */
	public function renderHTML($docData)
    {
        if ($this->config['debug'] === true) {
            $this->logger->info('PDFManager: docData', $docData);
        }

        // Set Twig Templates Folder
		$view = new \Slim\Views\Twig($docData['templatePath']);

        // Manage Array of Templates
		if(!is_array($docData['templateName'])){
			$docData['templateName'] = (array)$docData['templateName'];
		}

		// Start to Generate PDF.
		$mpdf = new \Mpdf\Mpdf(['tempDir' => $this->config['fullpath'].'/tmp', 's', 'A4', '', '', 20, 15, 20, 20, 5, 5]);
		$mpdf->SetDisplayMode('fullpage');
		$mpdf->PDFA = true;

		// Test per encoding su macchine di sviluppo
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='utf-8';

        if (isset($docData['templateHash']) && $docData['templateHash'] != "") {
            $mpdf->SetKeywords('h:' . $hash);
        }

		$mpdf->SetAuthor('Agenzia delle Dogane e dei Monopoli');

		foreach($docData['templateName'] as $template){
			$mpdf->AddPage();

            if (isset($docData['showNumPages']) && boolval($docData['showNumPages']) === true) {
                $mpdf->setFooter('Pagina {PAGENO} di {nb}','E|O');
            }

			$mpdf->WriteHTML(utf8_encode($this->view->render($response, $template, $docData['templateParams'])));
		}
		
		return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
	}
}

?>