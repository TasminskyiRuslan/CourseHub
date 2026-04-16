<?php

namespace App\Swagger\Auth\Requests;

use App\Enums\UserRole;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterUserRequest',
    title: 'Register User Request',
    description: 'Request payload for registering a new user.',
    required: ['name', 'email', 'password', 'password_confirmation', 'role'],
    properties: [
        new OA\Property(
            property: 'name',
            description: 'Full name of the user.',
            type: 'string',
            maxLength: 100,
            minLength: 2,
            example: 'John Doe',
            nullable: false
        ),
        new OA\Property(
            property: 'email',
            description: 'Email address of the user.',
            type: 'string',
            format: 'email',
            maxLength: 255,
            example: 'john@example.com',
            nullable: false
        ),
        new OA\Property(
            property: 'password',
            description: 'Account password of the user.',
            type: 'string',
            format: 'password',
            minLength: 8,
            example: 'password123',
            nullable: false
        ),
        new OA\Property(
            property: 'password_confirmation',
            description: 'Password confirmation of the user. (must match password)',
            type: 'string',
            format: 'password',
            minLength: 8,
            example: 'password123',
            nullable: false
        ),
        new OA\Property(
            property: 'role',
            description: 'Role of the user.',
            type: 'string',
            enum: [UserRole::STUDENT->value, UserRole::TEACHER->value],
            example: UserRole::STUDENT->value,
            nullable: false
        )
    ],
    type: 'object'
)]
class RegisterUserRequestSchema
{
}
