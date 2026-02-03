<?php

namespace App\Swagger\Schemas\Lessons;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Lesson',
    title: 'Lesson schema',
    description: 'Details of a lesson returned by the API',
    required: ['id', 'course_id', 'title', 'slug', 'position', 'content', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'The ID of the lesson',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'course_id',
            description: 'The ID of the course',
            type: 'integer',
            example: 10
        ),
        new OA\Property(
            property: 'title',
            description: 'The title of the lesson',
            type: 'string',
            example: 'Introduction to Algebra'
        ),
        new OA\Property(
            property: 'slug',
            description: 'The slug of the lesson',
            type: 'string',
            example: 'introduction-to-algebra'
        ),
        new OA\Property(
            property: 'position',
            description: 'The position of the lesson',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'content',
            description: 'The content of the lesson',
            oneOf: [
                new OA\Schema(ref: '#/components/schemas/OfflineLesson'),
                new OA\Schema(ref: '#/components/schemas/OnlineLesson'),
                new OA\Schema(ref: '#/components/schemas/VideoLesson'),
            ]
        ),
        new OA\Property(
            property: 'created_at',
            description: 'The creation date of the lesson',
            type: 'string',
            format: 'date-time',
            example: '2026-01-01T12:00:00Z'
        ),
        new OA\Property(
            property: 'updated_at',
            description: 'The modification date of the lesson',
            type: 'string',
            format: 'date-time',
            example: '2026-01-10T12:00:00Z'
        ),
    ],
    type: 'object'
)]
class LessonSchema
{
}
