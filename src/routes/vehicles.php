<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Created by PhpStorm.
 * User: Andress
 * Date: 9/11/17
 * Time: 6:06 PM
 */

$app->group('/vehicles', function()
{
    $this->get('', getVehicles);
})->add(new AuthMiddleware());

/*
 * =====================================================================================================================
 * Methods
 * =====================================================================================================================
 */

/**
 * Returns the available vehicles to be booked.
 *
 * @param Request $request
 * @param Response $response
 * @return mixed
 */
function getVehicles(Request $request, Response $response)
{
    $db = new Database();

    $query = sprintf("SELECT v.id, v.rego_no, v.odometer, vt.make, vt.model, vt.colour, l.address, l.suburb, l.postcode 
                             FROM vehicles v INNER JOIN vehicle_types vt ON v.type_id = vt.id 
                             INNER JOIN locations l ON v.location_id = l.id WHERE v.disposed IS NULL");

    $result = $db->get($query);

    if (count($result) != 0)
    {
        $payload = ['vehicles' => $result];
        $myResponse = new MyResponse(MyResponse::MSG_OK, $payload);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_OK);
    }
    else
    {
        $myResponse = new MyResponse(MyResponse::ERROR_VEHICLE_NOT_FOUND, null);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_NOT_FOUND);
    }
}