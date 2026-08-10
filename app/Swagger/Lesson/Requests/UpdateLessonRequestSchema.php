<?php

declare(strict_types=1);

namespace App\Swagger\Lesson\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateLessonRequest',
    title: 'Update Lesson Request',
    description: 'Payload for updating an existing lesson.',
    properties: [
        new OA\Property(
            property: 'title',
            description: 'Title.',
            type: 'string',
            maxLength: 255,
            example: 'Introduction to Geometry'
        ),
        new OA\Property(
            property: 'slug',
            description: 'Lesson slug.',
            type: 'string',
            maxLength: 255,
            pattern: '^[a-z0-9-]+$',
            example: 'introduction-to-geometry'
        ),
        new OA\Property(
            property: 'position',
            description: 'Position.',
            type: 'integer',
            minimum: 0,
            example: 1
        ),
    ],
    type: 'object',
    oneOf: [
        new OA\Schema(ref: '#/components/schemas/UpdateOfflineLessonRequest'),
        new OA\Schema(ref: '#/components/schemas/UpdateOnlineLessonRequest'),
        new OA\Schema(ref: '#/components/schemas/UpdateVideoLessonRequest'),
    ]
)]
class UpdateLessonRequestSchema {}
