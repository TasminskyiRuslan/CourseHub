<?php

namespace App\Swagger\Auth\Requests;

use App\Enums\UserRole;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterUserRequest',
    title: 'Register User Request',
    description: 'Payload for registering a new user auth.',
    required: ['name', 'email', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(
            property: 'name',
            description: 'Full name.',
            type: 'string',
            maxLength: 100,
            minLength: 2,
            example: 'John Doe'
        ),
        new OA\Property(
            property: 'email',
            description: 'Unique email address.',
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
            minLength: 8,
            example: 'password123'
        ),
        new OA\Property(
            property: 'password_confirmation',
            description: 'Password confirmation (must match password).',
            type: 'string',
            format: 'password',
            minLength: 8,
            example: 'password123'
        ),
        new OA\Property(
            property: 'roles',
            description: 'List of requested roles.',
            type: 'array',
            items: new OA\Items(
                type: 'string',
                enum: [
                    UserRole::TEACHER->value
                ]
            ),
            default: [],
            example: [
                UserRole::TEACHER->value
            ]
        )
    ],
    type: 'object'
)]
class RegisterUserRequestSchema
{
}
