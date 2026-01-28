<?php

namespace App\Swagger\Schemas\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Auth',
    title: 'Auth schema',
    description: 'Authentication data returned by the API',
    required: ['user', 'token', 'token_type', 'expires_at'],
    properties: [
        new OA\Property(
            property: 'user',
            ref: '#/components/schemas/User',
            description: 'Authenticated user data'
        ),
        new OA\Property(
            property: 'token',
            description: 'Access token',
            type: 'string',
            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'
        ),
        new OA\Property(
            property: 'token_type',
            description: 'Type of the token for Authorization header',
            type: 'string',
            example: 'Bearer'
        ),
        new OA\Property(
            property: 'expires_at',
            description: 'Expiration date of the token',
            type: 'string',
            format: 'date-time',
            example: '2026-01-28T12:00:00+02:00'
        ),
    ],
    type: 'object'
)]
class AuthSchema {}
