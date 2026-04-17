<?php

namespace App\Swagger\Auth\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ResetPasswordRequest',
    title: 'Reset Password Request',
    description: 'Request payload for resetting password.',
    required: ['email', 'password', 'password_confirmation', 'token'],
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
            minLength: 8,
            example: 'newPassword123'
        ),
        new OA\Property(
            property: 'password_confirmation',
            description: 'Password confirmation of the user. (must match password)',
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
