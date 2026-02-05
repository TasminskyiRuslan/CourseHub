<?php

namespace App\Swagger\Requests\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ResetPasswordRequest',
    title: 'Resend password request schema',
    description: 'Request schema for resetting a user password using a token from email.',
    required: ['email', 'password', 'password_confirmation', 'token'],
    properties: [
        new OA\Property(
            property: 'email',
            description: 'Email address',
            type: 'string',
            format: 'email',
            maxLength: 255,
            example: 'john@example.com'
        ),
        new OA\Property(
            property: 'password',
            description: 'The new password',
            type: 'string',
            format: 'password',
            minLength: 8,
            example: 'NewPassword123'
        ),
        new OA\Property(
            property: 'password_confirmation',
            description: 'The new password confirmation',
            type: 'string',
            format: 'password',
            minLength: 8,
            example: 'NewPassword123'
        ),
        new OA\Property(
            property: 'token',
            description: 'The password reset token received via email.',
            type: 'string',
            example: '660064...'
        )
    ],
    type: 'object'
)]
class ResetPasswordRequestSchema
{
}
