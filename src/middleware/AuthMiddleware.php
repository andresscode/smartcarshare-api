<?php
/**
 * Created by PhpStorm.
 * User: Andress
 * Date: 7/11/17
 * Time: 1:06 PM
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Firebase\JWT\JWT;

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