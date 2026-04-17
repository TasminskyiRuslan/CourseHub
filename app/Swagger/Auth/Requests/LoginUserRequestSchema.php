<?php

namespace App\Swagger\Auth\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LoginUserRequest',
    title: 'Login User Request',
    description: 'Request payload for authenticating a user.',
    required: ['email', 'password'],
    properties: [
        new OA\Property(
            property: 'email',
            description: 'Email address of the user.',
            type: 'string',
            format: 'email',
            maxLength: 255,
            example: 'john@example.com'
        ),
        new OA\Property(
            property: 'password',
            description: 'Account password of the user.',
            type: 'string',
            format: 'password',
            example: 'password123'
        ),
        new OA\Property(
            property: 'remember',
            description: 'Remember me flag of the user.',
            type: 'boolean',
            default: false,
            example: true
        )
    ],
    type: 'object'
)]
class LoginUserRequestSchema
{
}
