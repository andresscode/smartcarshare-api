<?php
/**
 * Created by PhpStorm.
 * User: Andress
 * Date: 4/11/17
 * Time: 4:01 PM
 */

use Firebase\JWT\JWT;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// URLs
$app->get('/auth/token', auth);

/*
 * =====================================================================================================================
 * Helper Methods
 * =====================================================================================================================
 */

const TOKEN_ENCODE_LENGTH = 32;
const NOT_BEFORE_TIME = 10;
const EXPIRE_TIME = 60;
const SECRET_KEY = 'holmesglensuckssomuch';

function auth(Request $request, Response $response)
{
    $username = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];

    $db = new Database();

    $data = $db->get("SELECT id, email, password FROM users WHERE email = \"$username\"");

    if (count($data) != 0)
    {
        $user = $data[0];

        $hash = $user->password;

        if (password_verify($password, $hash))
        {
            // Token setup
            $tokenId = base64_encode(random_bytes(TOKEN_ENCODE_LENGTH));
            $issuedAt = time();
            $notBefore = $issuedAt + NOT_BEFORE_TIME;
            $expire = $notBefore + EXPIRE_TIME;
            $serverName = gethostname();

            $data = array(
                'iat' => $issuedAt,
                'jti' => $tokenId,
                'iss' => $serverName,
                'nbf' => $notBefore,
                'exp' => $expire,
                'data' => array(
                    'userId' => $user->id,
                    'userEmail' => $user->email
                )
            );

            $jwt = JWT::encode(
                $data,
                SECRET_KEY,
                'HS256'
            );

            $payload = ['jwt' => $jwt];
            $myResponse = new MyResponse(MyResponse::MSG_USER_AUTHENTICATED, $payload);
            return $response->withJson($myResponse->getArray());
        }
        else
        {
            $myResponse = new MyResponse(MyResponse::MSG_PASSWORD_NOT_MATCH, null);
            return $response->withJson($myResponse->getArray(), MyResponse::HTTP_BAD_REQUEST);
        }
    }
    else
    {
        $myResponse = new MyResponse(MyResponse::MSG_USERNAME_NOT_FOUND, null);
        return $response->withJson($myResponse->getArray(), MyResponse::HTTP_BAD_REQUEST);
    }
}