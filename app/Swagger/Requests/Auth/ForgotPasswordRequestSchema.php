<?php

namespace App\Swagger\Requests\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ForgotPasswordRequest',
    title: 'Forgot password request schema',
    description: 'Schema for requesting a password reset link via API',
    required: ['email'],
    properties: [
        new OA\Property(
            property: 'email',
            description: 'User email address',
            type: 'string',
            format: 'email',
            maxLength: 255,
            example: 'john@example.com'
        ),
    ],
    type: 'object'
)]
class ForgotPasswordRequestSchema {}
