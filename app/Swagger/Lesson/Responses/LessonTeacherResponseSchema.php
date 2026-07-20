<?php

namespace App\Swagger\Lesson\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LessonTeacherResponse',
    title: 'Lesson Teacher Response',
    description: 'Teacher panel data of a lesson.',
    required: ['id', 'course_id', 'title', 'slug', 'position', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'Unique identifier.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'course_id',
            description: 'Course identifier.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'title',
            description: 'Title.',
            type: 'string',
            example: 'Introduction to Algebra'
        ),
        new OA\Property(
            property: 'slug',
            description: 'Lesson slug.',
            type: 'string',
            example: 'introduction-to-algebra'
        ),
        new OA\Property(
            property: 'position',
            description: 'Position.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'content',
            description: 'Polymorphic content of the lesson.',
            oneOf: [
                new OA\Schema(ref: '#/components/schemas/OfflineLessonResponse'),
                new OA\Schema(ref: '#/components/schemas/OnlineLessonResponse'),
                new OA\Schema(ref: '#/components/schemas/VideoLessonResponse'),
            ]
        ),
        new OA\Property(
            property: 'created_at',
            description: 'Creation time.',
            type: 'string',
            format: 'date-time',
            example: '2026-01-01T12:00:00.000000Z'
        ),
        new OA\Property(
            property: 'updated_at',
            description: 'Modification time.',
            type: 'string',
            format: 'date-time',
            example: '2026-01-10T12:00:00.000000Z'
        )
    ],
    type: 'object'
)]
class LessonTeacherResponseSchema
{

}
