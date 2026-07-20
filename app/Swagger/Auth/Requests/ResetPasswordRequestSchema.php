<?php

namespace App\Swagger\Auth\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ResetPasswordRequest',
    title: 'Reset Password Request',
    description: 'Payload for setting a new password using a token.',
    required: ['email', 'password', 'password_confirmation', 'token'],
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
            description: 'New account password.',
            type: 'string',
            format: 'password',
            minLength: 8,
            example: 'newPassword123'
        ),
        new OA\Property(
            property: 'password_confirmation',
            description: 'Password confirmation (must match password).',
            type: 'string',
            format: 'password',
            minLength: 8,
            example: 'newPassword123'
        ),
        new OA\Property(
            property: 'token',
            description: 'Password reset token received via email.',
            type: 'string',
            example: '66006454322443...'
        )
    ],
    type: 'object'
)]
class ResetPasswordRequestSchema
{
}
