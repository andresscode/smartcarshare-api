<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Created by PhpStorm.
 * User: Andress
 * Date: 5/11/17
 * Time: 9:58 PM
 */

// URLs
$app->group('/members', function()
{
    $this->post('', addMember);
    $this->get('/{id}', getMember);
    $this->put('/{id}', updateMember);
})->add(new AuthMiddleware());

/*
 * =====================================================================================================================
 * Methods
 * =====================================================================================================================
 */

// Members columns
const ID = 'id';
const USER_ID = 'user_id';
const LAST_NAME = 'last_name';
const FIRST_NAME = 'first_name';
const LICENCE_NO = 'licence_no';
const LICENCE_EXP = 'licence_exp';
const ADDRESS = 'address';
const SUBURB = 'suburb';
const POSTCODE = 'postcode';
const PHONE = 'phone';
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';

/**
 * Registers a new member in the database checking first is the member details already exists. user_id must be unique.
 *
 * @param Request $request
 * @param Response $response
 * @return mixed
 */
function addMember(Request $request, Response $response)
{
    $member = $request->getParsedBody();

    $userId = $request->getAttribute(TOKEN_DATA)->user_id;

    $db = new Database();

    $query = sprintf("SELECT id FROM members WHERE user_id = $userId");

    $result = $db->get($query);

    if (count($result) == 0)
    {
        $member[CREATED_AT] = date(DATE_TIME_FORMAT);
        $member[UPDATED_AT] = date(DATE_TIME_FORMAT);

        $query = sprintf("INSERT INTO members (user_id, last_name, first_name, licence_no, licence_exp, address, suburb, postcode, phone, created_at, updated_at) VALUES (%d, '%s', '%s', '%s', '%s', '%s', '%s', %d, '%s', '%s', '%s')",
            $userId,
            $member[LAST_NAME],
            $member[FIRST_NAME],
            $member[LICENCE_NO],
            $member[LICENCE_EXP],
            $member[ADDRESS],
            $member[SUBURB],
            $member[POSTCODE],
            $member[PHONE],
            $member[CREATED_AT],
            $member[UPDATED_AT]
        );

        $result = $db->post($query);

        if (is_numeric($result))
        {
            $member[ID] = $result;

            $payload = ['member' => $member];
            $myResponse = new MyResponse(MyResponse::MSG_MEMBER_CREATED, $payload);
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
        $myResponse = new MyResponse(MyResponse::ERROR_MEMBER_ALREADY_EXISTS, null);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_BAD_REQUEST);
    }
}

/**
 * Returns the member details, it uses the user_id instead of the id of the member table.
 *
 * @param Request $request
 * @param Response $response
 * @param $args
 * @return mixed
 */
function getMember(Request $request, Response $response, $args)
{
    $userId = $request->getAttribute(TOKEN_DATA)->user_id;

    if ($args['id'] == $userId)
    {
        $query = sprintf("SELECT * FROM members WHERE user_id = %d", $userId);

        $db = new Database();

        $result = $db->get($query);

        if (count($result) == 1)
        {
            $member = $result[0];

            $payload = ['member' => $member];

            $myResponse = new MyResponse(MyResponse::MSG_OK, $payload);
            return $response->withJson($myResponse->asArray(), MyResponse::HTTP_OK);
        }
        else
        {
            $myResponse = new MyResponse(MyResponse::ERROR_MEMBER_NOT_FOUND, null);
            return $response->withJson($myResponse->asArray(), MyResponse::HTTP_NOT_FOUND);
        }
    }
    else
    {
        $myResponse = new MyResponse(MyResponse::ERROR_FORBIDDEN, null);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_FORBIDDEN);
    }
}

/**
 * Updates the details of the member. Does not edit the user_id or the created_at date.
 *
 * @param Request $request
 * @param Response $response
 * @param $args
 * @return mixed
 */
function updateMember(Request $request, Response $response, $args)
{
    $userId = $request->getAttribute(TOKEN_DATA)->user_id;

    if ($args['id'] == $userId)
    {
        $member = $request->getParsedBody();

        $updatedAt = date(DATE_TIME_FORMAT);

        $query = sprintf("UPDATE members SET last_name = '%s', first_name = '%s', licence_no = '%s', licence_exp = '%s', address = '%s', suburb = '%s', postcode = %d, phone = '%s', updated_at = '%s' WHERE user_id = %d",
            $member[LAST_NAME],
            $member[FIRST_NAME],
            $member[LICENCE_NO],
            $member[LICENCE_EXP],
            $member[ADDRESS],
            $member[SUBURB],
            $member[POSTCODE],
            $member[PHONE],
            $updatedAt,
            $userId
        );

        $db = new Database();

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
        $myResponse = new MyResponse(MyResponse::ERROR_FORBIDDEN, null);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_FORBIDDEN);
    }
}