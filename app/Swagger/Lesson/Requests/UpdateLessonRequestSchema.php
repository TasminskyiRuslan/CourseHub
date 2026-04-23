<?php

namespace App\Swagger\Lesson\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateLessonRequest',
    title: 'Update Lesson Request',
    properties: [
        new OA\Property(
            property: 'title',
            description: 'Title of the lesson.',
            type: 'string',
            maxLength: 255,
            example: 'Introduction to Geometry'
        ),
        new OA\Property(
            property: 'slug',
            description: 'Slug of the lesson. (must be unique)',
            type: 'string',
            maxLength: 255,
            pattern: '^[a-z0-9-]+$',
            example: 'introduction-to-geometry'
        ),
        new OA\Property(
            property: 'position',
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
class  UpdateLessonRequestSchema
{
}
