<?php

namespace App\Swagger\Schemas\Auth;

use App\Enums\UserRole;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    title: 'User Schema',
    description: 'Details of a user returned by the API',
    required: ['id', 'name', 'slug', 'email', 'role', 'email_verified_at', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(
            property: 'data',
            properties: [
                new OA\Property(
                    property: 'id',
                    description: 'The user ID',
                    type: 'integer',
                    example: 1
                ),
                new OA\Property(
                    property: 'name',
                    description: 'The user name',
                    type: 'string',
                    example: 'John Doe'
                ),
                new OA\Property(
                    property: 'slug',
                    description: 'The user slug',
                    type: 'string',
                    example: 'john-doe'
                ),
                new OA\Property(
                    property: 'email',
                    description: 'The user email',
                    type: 'string',
                    format: 'email',
                    example: 'john@example.com'
                ),
                new OA\Property(
                    property: 'role',
                    description: 'The user role',
                    type: 'string',
                    enum: [UserRole::STUDENT->value, UserRole::TEACHER->value],
                    example: UserRole::STUDENT->value
                ),
                new OA\Property(
                    property: 'email_verified_at',
                    description: 'The user email verified at',
                    type: 'string',
                    format: 'date-time',
                    example: '2026-01-01T12:00:00Z',
                    nullable: true
                ),
                new OA\Property(
                    property: 'created_at',
                    description: 'The user registered at',
                    type: 'string',
                    format: 'date-time',
                    example: '2026-01-01T12:00:00Z'
                ),
                new OA\Property(
                    property: 'updated_at',
                    description: 'The user last updated at',
                    type: 'string',
                    format: 'date-time',
                    example: '2026-01-10T12:00:00Z'
                ),
            ]
        )
    ],
    type: 'object'
)]
class UserSchema
{
}
