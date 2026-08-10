<?php

declare(strict_types=1);

namespace App\Swagger\Lesson\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LessonStudentResponse',
    title: 'Lesson Student Response',
    description: 'Student panel data of a lesson.',
    required: ['id', 'course_id', 'title', 'slug', 'position'],
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
    ],
    type: 'object'
)]
class LessonStudentResponseSchema {}
