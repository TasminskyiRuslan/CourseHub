<?php

namespace App\Swagger\Requests\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LoginRequest',
    title: 'Login request schema',
    description: 'Schema for user login via API request',
    required: ['email', 'password'],
    properties: [
        new OA\Property(
            property: 'email',
            description: 'User registered email address',
            type: 'string',
            format: 'email',
            maxLength: 255,
            example: 'john@example.com'
        ),
        new OA\Property(
            property: 'password',
            description: 'User password',
            type: 'string',
            format: 'password',
            example: 'password123'
        ),
        new OA\Property(
            property: 'remember',
            description: 'Whether to stay logged in after the session expires',
            type: 'boolean',
            default: false,
            example: true
        ),
    ],
    type: 'object'
)]
class LoginRequestSchema
{
}
