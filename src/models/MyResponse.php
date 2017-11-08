<?php
/**
 * Created by PhpStorm.
 * User: Andress
 * Date: 5/11/17
 * Time: 12:40 PM
 *
 * This is the model for the response structure of the API. Every returning message from this API will have two
 * properties:
 *
 * 1. message: A string with information about the result of the request made by the client, i.e. "The user has been
 * authenticated successfully". This is useful to give some feedback to the programmer about the request that was
 * made.
 *
 * 2. payload: This property can be null, just, when the request made returns an error; otherwise, the payload will
 * be an Array with the data requested by the client, i.e.
 */
class MyResponse
{
    // HTTP status codes
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;

    // Error messages
    const ERROR_EMAIL_NOT_FOUND = 'The email does not exist in the database';
    const ERROR_PASSWORD_NOT_MATCH = 'The password does not match';
    const ERROR_MEMBER_ALREADY_EXISTS = 'The member already exists in the database';
    const ERROR_MEMBERSHIP_ALREADY_EXISTS = 'The member has a membership created already';
    const ERROR_MEMBER_NOT_FOUND = 'The member does not exist in the database';
    const ERROR_AUTH_HEADER_MISSING = 'The Authorization header is missing';
    const ERROR_AUTH_HEADER_FORMAT = 'The Authorization header format is not compatible';
    const ERROR_FORBIDDEN = 'You have no permission to access this resource';
    const ERROR_MISSING_BODY = 'There is no body in the request';

    // Returning messages
    const MSG_OK = 'OK';
    const MSG_USER_AUTHENTICATED = 'The user has been authenticated successfully';
    const MSG_USER_CREATED = 'The user has been created successfully';
    const MSG_MEMBER_CREATED = 'The member has been created successfully';
    const MSG_MEMBERSHIP_CREATED = 'The membership has been created successfully';
    const MSG_RESOURCE_UPDATED = 'The resource was updated successfully';

    // Fields
    private $array;

    public function MyResponse($message, $payload)
    {
        $this->array = array
        (
            'message' => $message,
            'payload' => $payload
        );
    }

    /**
     * Returns the content of the MyResponse object as an array to be send in the body of the Response as JSON.
     *
     * @return array Values of message and payload.
     */
    public function asArray() {
        return $this->array;
    }
}