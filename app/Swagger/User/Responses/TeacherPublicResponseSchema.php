<?php

declare(strict_types=1);

namespace App\Swagger\User\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TeacherPublicResponse',
    title: 'Teacher Public Response',
    description: 'Public profile data of a teacher.',
    required: ['id', 'name', 'slug', 'courses_count'],
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
            description: 'Teacher slug.',
            type: 'string',
            example: 'john-doe'
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
    ],
    type: 'object'
)]
class TeacherPublicResponseSchema {}
