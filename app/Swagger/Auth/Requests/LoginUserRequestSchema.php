<?php

declare(strict_types=1);

namespace App\Swagger\Auth\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LoginUserRequest',
    title: 'Login User Request',
    description: 'Payload for authenticating an existing user.',
    required: ['email', 'password'],
    properties: [
        new OA\Property(
            property: 'email',
            description: 'Email address.',
            type: 'string',
            format: 'email',
            maxLength: 255,
            example: 'john@example.com'
        ),
        new OA\Property(
            property: 'password',
            description: 'Account password.',
            type: 'string',
            format: 'password',
            example: 'password123'
        ),
        new OA\Property(
            property: 'remember',
            description: 'Remember me session flag.',
            type: 'boolean',
            default: false,
            example: false,
        ),
    ],
    type: 'object'
)]
class LoginUserRequestSchema {}
