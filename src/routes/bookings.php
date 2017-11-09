<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Created by PhpStorm.
 * User: Andress
 * Date: 9/11/17
 * Time: 7:51 PM
 */

// URLs
$app->group('/bookings', function()
{
    $this->post('', addBooking);
    $this->get('', getBookings);
    $this->put('/{id}', updateBooking);
})->add(new AuthMiddleware());

// Booking columns
const ID = 'id';
const MEMBERSHIP_ID = 'membership_id';
const VEHICLE_ID = 'vehicle_id';
const START_DATE = 'start_date';
const END_DATE = 'end_date';
const START_KMS = 'start_kms';
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';

/**
 * Registers a new booking for the user.
 *
 * @param Request $request
 * @param Response $response
 * @return mixed
 */
function addBooking(Request $request, Response $response)
{
    $db = new Database();

    $userId  = $request->getAttribute(TOKEN_DATA)->user_id;

    $query = sprintf("SELECT mp.id FROM users u INNER JOIN members m ON m.user_id = u.id INNER JOIN memberships mp ON mp.member_id = m.id WHERE u.id = %d", $userId);

    $result = $db->get($query);

    if (count($result) == 1)
    {
        $membershipId = $result[0]->id;

        $body = $request->getParsedBody();

        if ($body)
        {
            $query = sprintf("SELECT odometer FROM vehicles WHERE id = %d", $body[VEHICLE_ID]);

            $result = $db->get($query);

            $startKms = $result[0]->odometer;
            $createdAt = date(DATE_TIME_FORMAT);
            $updatedAt = date(DATE_TIME_FORMAT);

            $query = sprintf("INSERT INTO bookings (membership_id, vehicle_id, start_date, end_date, start_kms, created_at, updated_at) VALUES (%d, %d, '%s', '%s', %d, '%s', '%s')",
                $membershipId,
                $body[VEHICLE_ID],
                $body[START_DATE],
                $body[END_DATE],
                $startKms,
                $createdAt,
                $updatedAt
            );

            $result = $db->post($query);

            if (is_numeric($result))
            {
                $booking = [
                    ID => $result,
                    MEMBERSHIP_ID => $membershipId,
                    VEHICLE_ID => $body[VEHICLE_ID],
                    START_DATE => $body[START_DATE],
                    END_DATE => $body[END_DATE],
                    START_KMS => $startKms,
                    CREATED_AT => $createdAt,
                    UPDATED_AT => $updatedAt,
                ];

                $payload = ['booking' => $booking];

                $myResponse = new MyResponse(MyResponse::MSG_BOOKING_CREATED, $payload);
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
            $myResponse = new MyResponse(MyResponse::ERROR_MISSING_BODY, null);
            return $response->withJson($myResponse->asArray(), MyResponse::HTTP_BAD_REQUEST);
        }
    }
    else
    {
        $myResponse = new MyResponse(MyResponse::ERROR_MEMBERSHIP_NOT_FOUND, null);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_NOT_FOUND);
    }
}

/**
 * Returns all the bookings from a given membership.
 *
 * @param Request $request
 * @param Response $response
 * @return mixed
 */
function getBookings(Request $request, Response $response)
{
    $db = new Database();

    $userId = $request->getAttribute(TOKEN_DATA)->user_id;

    $query = sprintf("SELECT mp.id FROM users u INNER JOIN members m ON m.user_id = u.id INNER JOIN memberships mp ON mp.member_id = m.id WHERE u.id = %d", $userId);

    $result = $db->get($query);

    if (count($result) == 1)
    {
        $membershipId = $result[0]->id;

        $query = sprintf("SELECT * FROM bookings WHERE membership_id = %d", $membershipId);

        $result = $db->get($query);

        if (count($result) > 0)
        {
            $payload = ['bookings' => $result];

            $myResponse = new MyResponse(MyResponse::MSG_OK, $payload);
            return $response->withJson($myResponse->asArray(), MyResponse::HTTP_OK);
        }
        else
        {
            $myResponse = new MyResponse(MyResponse::ERROR_BOOKINGS_FOUND, null);
            return $response->withJson($myResponse->asArray(), MyResponse::HTTP_NOT_FOUND);
        }
    }
    else
    {
        $myResponse = new MyResponse(MyResponse::ERROR_MEMBERSHIP_NOT_FOUND, null);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_NOT_FOUND);
    }
}

/**
 * Updates a booking if the booking has not been completed yet.
 *
 * @param Request $request
 * @param Response $response
 * @param $args
 * @return mixed
 */
function updateBooking(Request $request, Response $response, $args)
{
    $db = new Database();

    $userId = $request->getAttribute(TOKEN_DATA)->user_id;

    $query = sprintf("SELECT mp.id FROM users u INNER JOIN members m ON m.user_id = u.id INNER JOIN memberships mp ON mp.member_id = m.id WHERE u.id = %d", $userId);

    $result = $db->get($query);

    if (count($result) == 1)
    {
        $membershipId = $result[0]->id;

        $query = sprintf("SELECT membership_id FROM bookings WHERE id = %d", $args[ID]);

        $result = $db->get($query);

        if ($membershipId == $result[0]->membership_id)
        {
            $query = sprintf("SELECT return_date FROM bookings WHERE id = %d", $args[ID]);

            $result = $db->get($query);

            if (count($result) == 1)
            {
                $booking = $result[0];

                if ($booking->return_date == null)
                {
                    $body = $request->getParsedBody();
                    $updatedAt = date(DATE_TIME_FORMAT);

                    if ($body)
                    {
                        $query = sprintf("UPDATE bookings SET vehicle_id = %d, start_date = '%s', end_date = '%s', updated_at = '%s' WHERE id = %d",
                            $body[VEHICLE_ID],
                            $body[START_DATE],
                            $body[END_DATE],
                            $updatedAt,
                            $args[ID]
                        );

                        $result = $db->put($query);

                        if ($result == true)
                        {
                            $myResponse = new MyResponse(MyResponse::MSG_RESOURCE_UPDATED, null);
                            return $response->withJson($myResponse->asArray(), MyResponse::HTTP_OK);
                        }
                        else
                        {
                            $myResponse = new MyResponse($result, null);
                            return $response->withJson($myResponse->asArray(), MyResponse::HTTP_BAD_REQUEST);
                        }
                    }
                    else
                    {
                        $myResponse = new MyResponse(MyResponse::ERROR_MISSING_BODY, null);
                        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_BAD_REQUEST);
                    }
                }
                else
                {
                    $myResponse = new MyResponse(MyResponse::ERROR_FORBIDDEN, null);
                    return $response->withJson($myResponse->asArray(), MyResponse::HTTP_FORBIDDEN);
                }
            }
            else
            {
                $myResponse = new MyResponse(MyResponse::ERROR_BOOKINGS_FOUND, null);
                return $response->withJson($myResponse->asArray(), MyResponse::HTTP_NOT_FOUND);
            }
        }
        else
        {
            $myResponse = new MyResponse(MyResponse::ERROR_FORBIDDEN, null);
            return $response->withJson($myResponse->asArray(), MyResponse::HTTP_FORBIDDEN);
        }
    }
    else
    {
        $myResponse = new MyResponse(MyResponse::ERROR_MEMBERSHIP_NOT_FOUND, null);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_NOT_FOUND);
    }
}