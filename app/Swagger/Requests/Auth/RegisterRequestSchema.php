<?php

namespace App\Swagger\Requests\Auth;

use App\Enums\UserRole;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterRequest',
    title: 'Register request schema',
    description: 'Schema for user registration via API request',
    required: ['name', 'email', 'password', 'password_confirmation', 'role'],
    properties: [
        new OA\Property(
            property: 'name',
            description: 'Name of the user',
            type: 'string',
            maxLength: 255,
            example: 'John Doe'
        ),
        new OA\Property(
            property: 'email',
            description: 'Email of the user',
            type: 'string',
            format: 'email',
            maxLength: 255,
            example: 'john@example.com'
        ),
        new OA\Property(
            property: 'password',
            description: 'Password of the user',
            type: 'string',
            format: 'password',
            minLength: 8,
            example: 'password123'
        ),
        new OA\Property(
            property: 'password_confirmation',
            description: 'Password Confirmation of the user',
            type: 'string',
            format: 'password',
            minLength: 8,
            example: 'password123'
        ),
        new OA\Property(
            property: 'role',
            description: 'Role of the user',
            type: 'string',
            enum: [UserRole::STUDENT->value, UserRole::TEACHER->value],
            example: UserRole::STUDENT->value
        ),
        new OA\Property(
            property: 'remember',
            description: 'Whether to stay logged in after the session expires',
            type: 'boolean',
            default: false,
            example: true,
        )
    ],
    type: 'object'
)]
class RegisterRequestSchema
{
}
