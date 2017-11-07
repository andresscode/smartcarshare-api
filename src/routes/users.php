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
$app->post('/users', addUser);

/*
 * =====================================================================================================================
 * Methods
 * =====================================================================================================================
 */

// Constants
const ROLE_VALUE = 'member';

// Users columns
const ID = 'id';
const EMAIL = 'email';
const PASSWORD = 'password';
const ROLE = 'role';
const CREATED_AT = 'createdAt';
const UPDATED_AT = 'updatedAt';

function addUser(Request $request, Response $response)
{
    $user = $request->getParsedBody();

    $user[PASSWORD] = password_hash($user[PASSWORD], PASSWORD_BCRYPT);
    $user[ROLE] = ROLE_VALUE;
    $user[CREATED_AT] = date(DATE_TIME_FORMAT);
    $user[UPDATED_AT] = date(DATE_TIME_FORMAT);

    $query = sprintf("INSERT INTO users (email, password, role, created_at, updated_at) VALUES ('%s', '%s', '%s', '%s', '%s')",
        $user[EMAIL],
        $user[PASSWORD],
        $user[ROLE],
        $user[CREATED_AT],
        $user[UPDATED_AT]
    );

    $db = new Database();

    $result = $db->post($query);

    if (is_numeric($result))
    {
        $user[ID] = $result;
        unset($user[PASSWORD]);

        $payload = ['user' => $user];
        $myResponse = new MyResponse(MyResponse::MSG_USER_CREATED, $payload);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_CREATED);
    }
    else
    {
        $myResponse = new MyResponse($result, null);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_BAD_REQUEST);
    }
}