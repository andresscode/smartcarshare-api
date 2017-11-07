<?php
/**
 * Created by PhpStorm.
 * User: Andress
 * Date: 5/11/17
 * Time: 9:58 PM
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// URLs
$app->post('/members', addMember)->add(new AuthMiddleware());

/*
 * =====================================================================================================================
 * Methods
 * =====================================================================================================================
 */

// Members columns
const ID = 'id';
const USER_ID = 'userId';
const LAST_NAME = 'lastName';
const FIRST_NAME = 'firstName';
const LICENCE_NO = 'licenceNo';
const LICENCE_EXP = 'licenceExp';
const ADDRESS = 'address';
const SUBURB = 'suburb';
const POSTCODE = 'postcode';
const PHONE = 'phone';
const CREATED_AT = 'createdAt';
const UPDATED_AT = 'updatedAt';

function addMember(Request $request, Response $response)
{
    $member = $request->getParsedBody();

    $userId = $request->getAttribute(TOKEN_DATA)->userId;

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