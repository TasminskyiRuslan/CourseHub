<?php

namespace App\Swagger\Auth\Responses;

use App\Enums\UserRole;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserResponse',
    title: 'User Response',
    description: 'Data of a specific user.',
    required: ['id', 'name', 'slug', 'email', 'role'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'Unique identifier of the user.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'name',
            description: 'Full name of the user.',
            type: 'string',
            example: 'John Doe'
        ),
        new OA\Property(
            property: 'slug',
            description: 'Slug of the user.',
            type: 'string',
            example: 'john-doe'
        ),
        new OA\Property(
            property: 'email',
            description: 'Email address of the user.',
            type: 'string',
            format: 'email',
            example: 'john@example.com'
        ),
        new OA\Property(
            property: 'role',
            description: 'Role of the user.',
            type: 'string',
            enum: [UserRole::STUDENT->value, UserRole::TEACHER->value],
            example: UserRole::STUDENT->value
        ),
        new OA\Property(
            property: 'email_verified_at',
            description: 'Time of the email verified at.',
            type: 'string',
            format: 'date-time',
            example: '2026-01-01T12:00:00Z',
            nullable: true
        )
    ],
    type: 'object'
)]
class UserResponseSchema
{
}
