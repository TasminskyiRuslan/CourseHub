<?php

declare(strict_types=1);

namespace App\Swagger\Auth\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthResponse',
    title: 'Auth Response',
    description: 'Data returned upon successful authentication.',
    required: ['user', 'access_token', 'token_type'],
    properties: [
        new OA\Property(
            property: 'user',
            ref: '#/components/schemas/UserAccountResponse',
            description: 'Authenticated user profile data.'
        ),
        new OA\Property(
            property: 'access_token',
            description: 'Personal access token for API requests.',
            type: 'string',
            example: '1|eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'
        ),
        new OA\Property(
            property: 'token_type',
            description: 'Authorization header prefix.',
            type: 'string',
            example: 'Bearer'
        ),
        new OA\Property(
            property: 'expires_at',
            description: 'Token expiration timestamp.',
            type: 'string',
            format: 'date-time',
            example: '2026-01-28T12:00:00.000000Z',
            nullable: true
        ),
    ],
    type: 'object'
)]
class AuthResponseSchema {}
