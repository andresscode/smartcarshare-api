<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Created by PhpStorm.
 * User: Andress
 * Date: 7/11/17
 * Time: 8:46 PM
 */

$app->group('/memberships', function()
{
    $this->post('', addMembership);
    $this->get('/types', getMembershipTypes);
})->add(new AuthMiddleware());

/*
 * =====================================================================================================================
 * Methods
 * =====================================================================================================================
 */

// Memberships columns
const ID = 'id';
const MEMBER_ID = 'member_id';
const MEMBERSHIP_TYPE_ID = 'membership_type_id';
const EXP_DATE = 'exp_date';
const TERMS_ACCEPTED = 'terms_accepted';

/**
 * Creates a new membership for a member. Checks if the member already has a membership before creates a new one
 * for the actual member.
 *
 * @param Request $request
 * @param Response $response
 * @return mixed
 */
function addMembership(Request $request, Response $response)
{
    $userId = $request->getAttribute(TOKEN_DATA)->user_id;

    $db = new Database();

    $query = sprintf("SELECT id FROM members WHERE user_id = %d", $userId);

    $result = $db->get($query);

    if (count($result) == 1)
    {
        $member = $result[0];

        $body = $request->getParsedBody();

        if ($body)
        {
            $termFile = sprintf("terms_%d.pdf", $member->id);
            $createdAt = date(DATE_TIME_FORMAT);
            $updatedAt = date(DATE_TIME_FORMAT);

            $query = $query = sprintf("SELECT * FROM memberships WHERE member_id = %d", $member->id);

            $result = $db->get($query);

            if (count($result) == 0)
            {
                $query = sprintf("INSERT INTO memberships (member_id, membership_type_id, exp_date, terms_accepted, terms_file, created_at, updated_at) VALUES (%d, %d, '%s', %d, '%s', '%s', '%s')",
                    $member->id,
                    $body[MEMBERSHIP_TYPE_ID],
                    $body[EXP_DATE],
                    $body[TERMS_ACCEPTED],
                    $termFile,
                    $createdAt,
                    $updatedAt
                );

                $result = $db->post($query);

                if (is_numeric($result))
                {
                    $query = sprintf("SELECT * FROM memberships WHERE member_id = %d", $member->id);

                    $result = $db->get($query);

                    $payload = ['membership' => $result[0]];

                    $myResponse = new MyResponse(MyResponse::MSG_MEMBERSHIP_CREATED, $payload);
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
                $myResponse = new MyResponse(MyResponse::ERROR_MEMBERSHIP_ALREADY_EXISTS, null);
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
        $myResponse = new MyResponse(MyResponse::ERROR_MEMBER_NOT_FOUND, null);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_NOT_FOUND);
    }
}

/**
 * Returns the membership types available to choose.
 *
 * @param Request $request
 * @param Response $response
 * @return mixed
 */
function getMembershipTypes(Request $request, Response $response)
{
    $db = new Database();

    $query = sprintf("SELECT * FROM membership_types");

    $result = $db->get($query);

    if (count($result) > 0)
    {
        $payload = ['membership_types' => $result];

        $myResponse = new MyResponse(MyResponse::MSG_OK, $payload);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_OK);
    }
    else
    {
        $myResponse = new MyResponse(MyResponse::ERROR_MEMBERSHIP_NOT_FOUND, null);
        return $response->withJson($myResponse->asArray(), MyResponse::HTTP_NOT_FOUND);
    }
}