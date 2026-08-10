<?php

declare(strict_types=1);

namespace App\Swagger\User\Responses;

use App\Enums\UserRole;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserAdminResponse',
    title: 'User Admin Response',
    description: 'Full user data for administrative management.',
    required: ['id', 'name', 'slug', 'email', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'Unique identifier.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'name',
            description: 'Full name.',
            type: 'string',
            example: 'John Doe'
        ),
        new OA\Property(
            property: 'slug',
            description: 'User slug.',
            type: 'string',
            example: 'john-doe'
        ),
        new OA\Property(
            property: 'email',
            description: 'Email address.',
            type: 'string',
            format: 'email',
            example: 'john@example.com'
        ),
        new OA\Property(
            property: 'email_verified_at',
            description: 'Verification time.',
            type: 'string',
            format: 'date-time',
            example: '2026-01-01T12:00:00.000000Z',
            nullable: true
        ),
        new OA\Property(
            property: 'roles',
            description: 'List of assigned roles.',
            type: 'array',
            items: new OA\Items(
                type: 'string',
                enum: [
                    UserRole::TEACHER->value,
                    UserRole::ADMIN->value,
                    UserRole::SUPER_ADMIN->value,
                ]
            ),
            example: [UserRole::TEACHER->value]
        ),
        new OA\Property(
            property: 'avatar_url',
            description: 'Public URL to the avatar image.',
            type: 'string',
            format: 'uri',
            example: 'http://localhost:8080/storage/users/avatar.png',
            nullable: true
        ),
        new OA\Property(
            property: 'courses_count',
            description: 'Total number of published courses.',
            type: 'integer',
            example: 5
        ),
        new OA\Property(
            property: 'banned_at',
            description: 'Banning time.',
            type: 'string',
            format: 'date-time',
            example: null,
            nullable: true
        ),
        new OA\Property(
            property: 'created_at',
            description: 'Creation timestamp.',
            type: 'string',
            format: 'date-time',
            example: '2026-01-01T12:00:00.000000Z'
        ),
        new OA\Property(
            property: 'updated_at',
            description: 'Modification timestamp.',
            type: 'string',
            format: 'date-time',
            example: '2026-01-10T12:00:00.000000Z'
        ),
        new OA\Property(
            property: 'deleted_at',
            description: 'Soft deletion timestamp.',
            type: 'string',
            format: 'date-time',
            example: null,
            nullable: true
        ),
    ],
    type: 'object'
)]
class UserAdminResponseSchema {}
