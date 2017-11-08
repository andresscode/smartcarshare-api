<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Firebase\JWT\JWT;

/**
 * Created by PhpStorm.
 * User: Andress
 * Date: 7/11/17
 * Time: 1:06 PM
 *
 * Handles the authorization process checking if the requests have a valid JWT, following this process:
 *
 * 1. Gets the Authorization header from the request.
 * 2. Checks if the Authorization header was sent.
 * 3. Checks if the Authorization header has the right format [Bearer].
 * 4. Tries to decode the JWT.
 * 4.1. If the JWT is invalid prompts the error message.
 * 4.2. If the JWT is valid continues to the next route passing the data from the token.
 */

class AuthMiddleware
{
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $authHeader = $request->getHeader('Authorization');

        if (count($authHeader) == 1)
        {
            $jwt = substr($authHeader[0], 7);

            if ($jwt)
            {
                try
                {
                    $token = JWT::decode($jwt, SECRET_KEY, array('HS256'));

                    $data = $token->data;

                    $response = $next($request->withAttribute(TOKEN_DATA, $data), $response);

                    return $response;
                }
                catch (Exception $e)
                {
                    $myResponse = new MyResponse($e->getMessage(), null);
                    return $response->withJson($myResponse->asArray(), MyResponse::HTTP_UNAUTHORIZED);
                }
            }
            else
            {
                $myResponse = new MyResponse(MyResponse::ERROR_AUTH_HEADER_FORMAT, null);
                return $response->withJson($myResponse->asArray(), MyResponse::HTTP_BAD_REQUEST);
            }
        }
        else
        {
            $myResponse = new MyResponse(MyResponse::ERROR_AUTH_HEADER_MISSING, null);
            return $response->withJson($myResponse->asArray(), MyResponse::HTTP_BAD_REQUEST);
        }
    }
}