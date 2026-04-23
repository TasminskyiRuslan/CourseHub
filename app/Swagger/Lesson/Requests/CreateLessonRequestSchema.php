<?php

namespace App\Swagger\Lesson\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateLessonRequest',
    title: 'Create Lesson Request',
    description: 'Request payload for creating a new lesson.',
    required: ['title'],
    properties: [
        new OA\Property(
            property: 'title',
            description: 'Title of the lesson.',
            type: 'string',
            maxLength: 255,
            example: 'Introduction to Algebra'
        ),
        new OA\Property(
            property: 'slug',
            description: 'Slug of the lesson. (optional, must be unique if provided)',
            type: 'string',
            maxLength: 255,
            pattern: '^[a-z0-9-]+$',
            example: 'introduction-to-algebra',
            nullable: true
        ),
        new OA\Property(
            property: 'position',
            type: 'integer',
            minimum: 0,
            example: 1,
            nullable: true
        ),
    ],
    type: 'object',
    oneOf: [
        new OA\Schema(ref: '#/components/schemas/CreateOfflineLessonRequest'),
        new OA\Schema(ref: '#/components/schemas/CreateOnlineLessonRequest'),
        new OA\Schema(ref: '#/components/schemas/CreateVideoLessonRequest'),
    ]
)]
class CreateLessonRequestSchema
{
}
