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
$app->post('/members', addMember);

/*
 * =====================================================================================================================
 * Methods
 * =====================================================================================================================
 */

// Constants
const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

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

    $member[CREATED_AT] = date(DATE_TIME_FORMAT);
    $member[UPDATED_AT] = date(DATE_TIME_FORMAT);

    $query = sprintf("INSERT INTO members (user_id, last_name, first_name, licence_no, licence_exp, address, suburb, postcode, phone, created_at, updated_at) VALUES (%d, '%s', '%s', '%s', '%s', '%s', '%s', %d, '%s', '%s', '%s')",
        $member[USER_ID],
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

    $db = new Database();

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