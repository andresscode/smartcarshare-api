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
 * Helper Methods
 * =====================================================================================================================
 */

const ROLE = 'member';
const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

function addMember(Request $request, Response $response)
{
    $member = $request->getParsedBody();

    $member['password'] = password_hash($member['password'], PASSWORD_BCRYPT);
    $member['role'] = ROLE;
    $member['createdAt'] = date(DATE_TIME_FORMAT);
    $member['updatedAt'] = date(DATE_TIME_FORMAT);

    $query = sprintf("INSERT INTO users (email, password, role, created_at, updated_at) VALUES ('%s', '%s', '%s', '%s', '%s')",
        $member['email'],
        $member['password'],
        $member['role'],
        $member['createdAt'],
        $member['updatedAt']
    );

    $db = new Database();

    $result = $db->post($query);

    if (is_numeric($result))
    {
        $member['id'] = $result;
        unset($member['password']);

        $payload = ['member' => $member];
        $myResponse = new MyResponse(MyResponse::MSG_MEMBER_CREATED, $payload);
        return $response->withJson($myResponse->getArray(), MyResponse::HTTP_CREATED);
    }
    else
    {
        $myResponse = new MyResponse($result, null);
        return $response->withJson($myResponse->getArray(), MyResponse::HTTP_BAD_REQUEST);
    }
}