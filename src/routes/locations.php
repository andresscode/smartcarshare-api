<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Created by PhpStorm.
 * User: Andress
 * Date: 9/11/17
 * Time: 6:43 PM
 */

$app->group('/locations', function()
{
    $this->get('', getLocations);
})->add(new AuthMiddleware());

/*
 * =====================================================================================================================
 * Methods
 * =====================================================================================================================
 */

function getLocations(Request $request, Response $response)
{
    $db = new Database();

    $query = sprintf("SELECT address, suburb, postcode FROM locations");

    $result = $db->get($query);

    if (count($result) != 0)
    {
        $payload = ['locations' => $result];
        $myResponse = new MyResponse(MyResponse::MSG_OK, $payload);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_OK);
    }
    else
    {
        $myResponse = new MyResponse(MyResponse::ERROR_LOCATION_NOT_FOUND, null);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_NOT_FOUND);
    }
}