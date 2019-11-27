<?php

namespace NIM_Backend\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AuthenticationMiddleware {

    protected $logger;

    public function __construct(\Monolog\Logger $logger)
    {
        global $appConfig;

        $this->logger = $logger;
        $this->config = $appConfig;
    }

    public function __invoke($request, $response, $next)
    {
        global $appconfig;

        return $response = $next($request, $response);
    }

}

?>