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
$app->group('/users', function()
{
    $this->post('', addUser);

    $this->group('', function()
    {
        $this->put('/{id}/changePassword', changePassword);
    })->add(new AuthMiddleware());
});

/*
 * =====================================================================================================================
 * Methods
 * =====================================================================================================================
 */

// Constants
const ROLE_VALUE = 'member';
const OLD_PASSWORD = 'old_password';
const NEW_PASSWORD = 'new_password';

// Users columns
const ID = 'id';
const EMAIL = 'email';
const PASSWORD = 'password';
const ROLE = 'role';
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at';

/**
 * Creates a new user into the database.
 *
 * @param Request $request
 * @param Response $response
 * @return mixed
 */
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

/**
 * Updates the password in the database, takes the current password to compare with the hash stored in the database.
 *
 * @param Request $request
 * @param Response $response
 * @param $args
 * @return mixed
 */
function changePassword(Request $request, Response $response, $args)
{
    $tokenUserId = $request->getAttribute(TOKEN_DATA)->user_id;

    if ($args['id'] == $tokenUserId)
    {
        $body = $request->getParsedBody();

        if ($body)
        {
            $db = new Database();

            $query = sprintf("SELECT password FROM users WHERE id = %d", $tokenUserId);

            $result = $db->get($query);

            $hash = $result[0]->password;

            var_dump("hash = " . $hash);

            $oldPassword = $body[OLD_PASSWORD];

            var_dump("oldPass = " . $oldPassword);

            if (password_verify($oldPassword, $hash))
            {
                $newPasswordHash = password_hash($body[NEW_PASSWORD], PASSWORD_BCRYPT);

                var_dump("newPass = " . $newPasswordHash);

                $query = sprintf("UPDATE users SET password = '%s' WHERE id = %d", $newPasswordHash, $tokenUserId);

                $result = $db->put($query);

                var_dump("result = " . $result);

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
                $myResponse = new MyResponse(MyResponse::ERROR_PASSWORD_NOT_MATCH, null);
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