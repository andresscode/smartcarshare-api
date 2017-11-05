<?php
/**
 * Created by PhpStorm.
 * User: Andress
 * Date: 5/11/17
 * Time: 12:40 PM
 */

class MyResponse
{
    // Constants Status codes
    const HTTP_BAD_REQUEST = 400;
    const HTTP_CREATED = 201;

    // Returning messages
    const MSG_USERNAME_NOT_FOUND = 'The email does not exist in the database';
    const MSG_USER_AUTHENTICATED = 'The user has been authenticated successfully';
    const MSG_PASSWORD_NOT_MATCH = 'The password is invalid';
    const MSG_MEMBER_CREATED = 'The member has been created successfully';

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

    public function getArray() {
        return $this->array;
    }
}