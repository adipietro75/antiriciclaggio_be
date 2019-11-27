<?php

namespace NIM_Backend\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use NIM_Backend\SharedLibs\ATManager;
use NIM_Backend\SharedLibs\CSVManager;
use NIM_Backend\SharedLibs\PDFManager;

class sharedController
{
    protected $logger;
    protected $db;

    public function __construct(\Monolog\Logger $logger, $db)
    {
        global $appConfig;

        $this->db     = $db;
        $this->logger = $logger;
        $this->config = $appConfig;
    }

    public function checkAUDM (Request $request, Response $response, $args)
    {
        $atmanager = new ATManager($this->logger);
        $sogdata   = $atmanager->getSoggetto($args['cf']);

        if (is_array($sogdata)) {
            $data = [ "status" => "OK", "result" => $sogdata ];
            return $response->withJson($data, 200);
        } else {
            $data = [ "status" => "NOK", "result" => "NOT_FOUND" ];
            return $response->withJson($data, 404);
        }
    }

    public function docGen(Request $request, Response $response, $args)
    {
        $postedData = $request->getParsedBody();

        if ($args['type'] == 'CSV') {
            $expectedData = [ 'datasetList', 'fileName', 'nomiColonna' ];
        } elseif ($args['type'] == 'PDF') {
            $expectedData = [ 'templateName', 'templatePath', 'templateParams', 'templateHash', 'showNumPages' ];
        } else {
            $data = [ "status" => "NOK", "result" => "UNEXPECTED_DOC_TYPE" ];
            return $response->withJson($data, 500);
        }

        foreach($postedData as $key => $param) {
            if (!in_array($key, $expectedData)) {
                $this->logger->info('sharedController | docGen: Parametro NON Previsto', [ $key ], [ $param ]);

                $data = [ "status" => "NOK", "result" => "UNEXPECTED_POST_DATA" ];
                return $response->withJson($data, 500);
            }
        }

        if ($args['type'] == 'CSV') {
            $csvmanager = new CSVManager($this->logger);
            $document   = $csvmanager->renderCSV($postedData);
            $mimetype   = 'text/csv';
        } elseif ($args['type'] == 'PDF') {
            $pdfmanager = new PDFManager($this->logger);
            $document   = $pdfmanager->renderPDF($postedData);
            $mimetype   = 'application/pdf';
        }

        $this->docRes($args['mode'], $mimetype, $document);
    }

    private function docRes($mode, $mimetype, $document)
    {
        if (isset($mode) && $mode == "base64") {
            $data = [ "status" => "OK", "result" => [ 'mime' => $mimetype, 'raw' => base64_encode($document) ] ];
            return $response->withJson($data, 200);
        } else if (isset($mode) && $mode == "inline") {
            $response = $this->response->withHeader( 'Content-type', $mimetype );
            return $response->write($document);
        } else {
            $data = [ "status" => "NOK", "result" => "NO_RESMODE_DEFINED" ];
            return $response->withJson($data, 500);
        }
    }

}

?>