<?php

namespace App\Swagger\Schemas\Courses;

use App\Enums\CourseType;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Course',
    title: 'Course schema',
    description: 'Details of a course returned by the API',
    required: ['id', 'author_id', 'author', 'title', 'slug', 'description', 'type', 'price', 'image_url', 'is_published', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'The ID of the course',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'author_id',
            description: 'The author of the lesson',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'author',
            ref: '#/components/schemas/Author',
            description: 'The author of the lesson',
        ),
        new OA\Property(
            property: 'title',
            description: 'The title of the lesson',
            type: 'string',
            example: 'Math 101'
        ),
        new OA\Property(
            property: 'slug',
            description: 'The slug of the lesson',
            type: 'string',
            example: 'math-101'
        ),
        new OA\Property(
            property: 'description',
            description: 'The description of the lesson',
            type: 'string',
            example: 'A basic mathematics course',
            nullable: true
        ),
        new OA\Property(
            property: 'type',
            description: 'The type of the lesson',
            type: 'string',
            enum: [CourseType::ONLINE->value, CourseType::OFFLINE->value, CourseType::VIDEO->value],
            example: CourseType::ONLINE->value
        ),
        new OA\Property(
            property: 'price',
            description: 'The price of the lesson',
            type: 'string',
            format: 'float',
            example: 99.99
        ),
        new OA\Property(
            property: 'image_url',
            description: 'The image of the lesson',
            type: 'string',
            format: 'uri',
            example: 'https://example.com/images/course1.png',
            nullable: true
        ),
        new OA\Property(
            property: 'is_published',
            description: 'The status of the lesson',
            type: 'boolean',
            example: true
        ),
        new OA\Property(
            property: 'created_at',
            description: 'The creation date of the lesson',
            type: 'string',
            format: 'date-time',
            example: '2026-01-20T12:00:00Z'
        ),
        new OA\Property(
            property: 'updated_at',
            description: 'The modification date of the course',
            type: 'string',
            format: 'date-time',
            example: '2026-01-25T12:00:00Z'
        ),
    ],
    type: 'object'
)]
class CourseSchema {}
