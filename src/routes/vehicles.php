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
    $this->post('/{id}/reviews', addReview);
    $this->get('/{id}/reviews', getReviews);
})->add(new AuthMiddleware());

/*
 * =====================================================================================================================
 * Methods
 * =====================================================================================================================
 */

// Review columns
const ID = 'id';
const VEHICLE_ID = 'vehicle_id';
const AUTHOR = 'author';
const DESCRIPTION = 'description';

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

/**
 * Inserts a review to a vehicle creating the author name.
 *
 * @param Request $request
 * @param Response $response
 * @param $args
 * @return mixed
 */
function addReview(Request $request, Response $response, $args)
{
    $body = $request->getParsedBody();

    if ($body)
    {
        $db = new Database();

        $userId = $request->getAttribute(TOKEN_DATA)->user_id;

        $query = sprintf("SELECT CONCAT(m.first_name, ' ', m.last_name) AS name FROM members m INNER JOIN users u ON m.user_id = u.id WHERE u.id = %d", $userId);

        $result = $db->get($query);

        if (count($result) == 1)
        {
            $author = $result[0]->name;

            $query = sprintf("INSERT INTO vehicle_reviews (vehicle_id, author, description) VALUES (%d, '%s', '%s')",
                $args[ID],
                $author,
                $body[DESCRIPTION]
            );

            $result = $db->post($query);

            if (is_numeric($result))
            {
                $review = [
                    ID => $result,
                    VEHICLE_ID => $args[ID],
                    AUTHOR => $author,
                    DESCRIPTION => $body[DESCRIPTION]
                ];

                $payload = ['review' => $review];

                $myResponse = new MyResponse(MyResponse::MSG_REVIEW_CREATED, $payload);
                return $response->withJson($myResponse->asArray(), MyResponse::HTTP_CREATED);
            }
            else
            {
                $myResponse = new MyResponse($result, null);
                return $response->withJson($myResponse->asArray(), MyResponse::HTTP_BAD_REQUEST);
            }
        }
        else
        {
            $myResponse = new MyResponse(MyResponse::ERROR_MEMBER_NOT_FOUND, null);
            return $response->withJson($myResponse->asArray(), MyResponse::HTTP_NOT_FOUND);
        }
    }
    else
    {
        $myResponse = new MyResponse(MyResponse::ERROR_MISSING_BODY, null);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_BAD_REQUEST);
    }
}

/**
 * Returns the reviews of a given vehicle.
 *
 * @param Request $request
 * @param Response $response
 * @param $args
 * @return mixed
 */
function getReviews(Request $request, Response $response, $args)
{
    $db = new Database();

    $query = sprintf("SELECT author, description FROM vehicle_reviews WHERE vehicle_id = %d", $args[ID]);

    $result = $db->get($query);

    if (count($result) > 0)
    {
        $payload = ['reviews' => $result];

        $myResponse = new MyResponse(MyResponse::MSG_OK, $payload);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_OK);
    }
    else
    {
        $myResponse = new MyResponse(MyResponse::ERROR_VEHICLE_REVIEWS_NOT_FOUND, null);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_NOT_FOUND);
    }
}