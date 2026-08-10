<?php

declare(strict_types=1);

namespace App\Swagger\Course\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateCourseRequest',
    title: 'Update Course Request',
    description: 'Payload for updating an existing course.',
    properties: [
        new OA\Property(
            property: 'title',
            description: 'Title.',
            type: 'string',
            maxLength: 255,
            example: 'Math 102'
        ),
        new OA\Property(
            property: 'slug',
            description: 'Course slug.',
            type: 'string',
            maxLength: 255,
            pattern: '^[a-z0-9-]+$',
            example: 'math-102'
        ),
        new OA\Property(
            property: 'description',
            description: 'Description.',
            type: 'string',
            maxLength: 5000,
            example: 'An advanced mathematics course',
            nullable: true
        ),
        new OA\Property(
            property: 'price',
            description: 'Price value.',
            type: 'string',
            maximum: 99999999.99,
            minimum: 0,
            example: '299.99'
        ),
    ],
    type: 'object'
)]
class UpdateCourseRequestSchema {}
