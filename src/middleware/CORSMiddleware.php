<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Created by PhpStorm.
 * User: Andress
 * Date: 7/11/17
 * Time: 7:10 PM
 *
 * This middleware handles the Cross-Origin Resource Sharing support for web browsers. This middleware is attached
 * to every route in the server.
 */

class CORSMiddleware
{
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $response = $next($request, $response);
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }
}