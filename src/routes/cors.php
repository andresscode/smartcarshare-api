<?php
/**
 * Created by PhpStorm.
 * User: Andress
 * Date: 7/11/17
 * Time: 7:18 PM
 *
 * This route receives any request to any endpoint for the OPTIONS HTTP method when any browser or client is
 * requesting for Cross-origin support.
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->options('/{routes:.+}', callCORSMiddleware);

function callCORSMiddleware(Request $request, Response $response)
{
    return $response;
}