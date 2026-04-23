<?php

namespace App\Swagger\Auth\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SendPasswordResetEmailRequest',
    title: 'Send Password Reset Email Request',
    description: 'Request payload for sending password reset link.',
    required: ['email'],
    properties: [
        new OA\Property(
            property: 'email',
            description: 'Email address of the user.',
            type: 'string',
            format: 'email',
            maxLength: 255,
            example: 'john@example.com',
        ),
    ],
    type: 'object'
)]
class SendPasswordResetEmailRequestSchema
{
}
