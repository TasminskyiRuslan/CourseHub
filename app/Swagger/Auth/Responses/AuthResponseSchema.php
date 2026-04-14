<?php

namespace App\Swagger\Auth\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthResponse',
    title: 'Auth Response',
    description: 'Data returned after user login or registration.',
    required: ['user', 'access_token', 'token_type', 'expires_at'],
    properties: [
        new OA\Property(
            property: 'user',
            ref: '#/components/schemas/UserResponse',
            description: 'Authenticated user data.'
        ),
        new OA\Property(
            property: 'access_token',
            description: 'Personal access token for API authentication.',
            type: 'string',
            example: '1|eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'
        ),
        new OA\Property(
            property: 'token_type',
            description: 'Token type to be used in the Authorization header.',
            type: 'string',
            example: 'Bearer'
        ),
        new OA\Property(
            property: 'expires_at',
            description: 'Time of the token expiration.',
            type: 'string',
            format: 'date-time',
            example: '2026-01-28T12:00:00+02:00'
        ),
    ],
    type: 'object'
)]
class AuthResponseSchema
{
}
