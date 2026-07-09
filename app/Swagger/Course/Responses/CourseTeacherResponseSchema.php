<?php

namespace App\Swagger\Course\Responses;

use App\Enums\CourseType;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CourseTeacherResponse',
    title: 'Course Teacher Response',
    description: 'Teacher panel data of a course.',
    required: ['id', 'author_id', 'title', 'slug', 'type', 'price', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'Unique identifier.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'author_id',
            description: 'Author identifier.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'title',
            description: 'Title.',
            type: 'string',
            example: 'Math 101'
        ),
        new OA\Property(
            property: 'slug',
            description: 'Course slug.',
            type: 'string',
            example: 'math-101'
        ),
        new OA\Property(
            property: 'description',
            description: 'Description.',
            type: 'string',
            example: 'A basic mathematics course',
            nullable: true
        ),
        new OA\Property(
            property: 'type',
            description: 'Type.',
            type: 'string',
            enum: [CourseType::ONLINE->value, CourseType::OFFLINE->value, CourseType::VIDEO->value],
            example: CourseType::ONLINE->value
        ),
        new OA\Property(
            property: 'price',
            description: 'Price.',
            type: 'string',
            format: 'float',
            example: 99.99
        ),
        new OA\Property(
            property: 'image_url',
            description: 'Public URL to the image.',
            type: 'string',
            format: 'uri',
            example: 'http://loclhost:8080/storage/courses/course1.png',
            nullable: true
        ),
        new OA\Property(
            property: 'lessons_count',
            description: 'Total number of lessons.',
            type: 'integer',
            example: 10,
        ),
        new OA\Property(
            property: 'published_at',
            description: 'Publishing time.',
            type: 'string',
            format: 'date-time',
            example: '2026-01-25T12:00:00.000000Z',
            nullable: true
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
            description: 'Creation time.',
            type: 'string',
            format: 'date-time',
            example: '2026-01-20T12:00:00.000000Z'
        ),
        new OA\Property(
            property: 'updated_at',
            description: 'Modification time.',
            type: 'string',
            format: 'date-time',
            example: '2026-01-25T12:00:00.000000Z'
        )
    ],
    type: 'object'
)]
class CourseTeacherResponseSchema
{
}
