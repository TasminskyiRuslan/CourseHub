<?php

namespace App\Swagger\Auth\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SendPasswordResetEmailRequest',
    title: 'Send Password Reset Email Request',
    description: 'Payload for requesting a password reset link.',
    required: ['email'],
    properties: [
        new OA\Property(
            property: 'email',
            description: 'Email address.',
            type: 'string',
            format: 'email',
            maxLength: 255,
            example: 'john@example.com'
        )
    ],
    type: 'object'
)]
class SendPasswordResetEmailRequestSchema
{
}
