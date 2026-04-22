<?php

namespace App\Swagger\Lesson\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LessonResponse',
    title: 'Data of a specific lesson.',
    required: ['id', 'course_id', 'title', 'slug', 'position', 'content', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'Unique identifier of the lesson.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'course_id',
            description: 'Course unique identifier of the lesson.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'title',
            description: 'Title of the lesson.',
            type: 'string',
            example: 'Introduction to Algebra'
        ),
        new OA\Property(
            property: 'slug',
            description: 'Slug of the lesson.',
            type: 'string',
            example: 'introduction-to-algebra'
        ),
        new OA\Property(
            property: 'position',
            description: 'Position of the lesson.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'content',
            description: 'Content of the lesson.',
            oneOf: [
                new OA\Schema(ref: '#/components/schemas/OfflineLessonResponse'),
                new OA\Schema(ref: '#/components/schemas/OnlineLessonResponse'),
                new OA\Schema(ref: '#/components/schemas/VideoLessonResponse'),
            ]
        ),
        new OA\Property(
            property: 'created_at',
            description: 'Creation date of the lesson.',
            type: 'string',
            format: 'date-time',
            example: '2026-01-01T12:00:00Z'
        ),
        new OA\Property(
            property: 'updated_at',
            description: 'Modification date of the lesson.',
            type: 'string',
            format: 'date-time',
            example: '2026-01-10T12:00:00Z'
        ),
    ],
    type: 'object'
)]
class LessonResponseSchema
{

}
