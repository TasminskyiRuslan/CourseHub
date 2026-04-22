<?php

namespace App\Swagger\Course\Responses;

use App\Enums\CourseType;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CourseResponse',
    title: 'Course Response',
    description: 'Data of a specific course.',
    required: ['id', 'author_id', 'title', 'slug', 'description', 'type', 'price', 'image_url', 'is_published', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'Unique identifier of the course',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'author_id',
            description: 'Author unique identifier of the course',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'author',
            ref: '#/components/schemas/Author',
            description: 'Author of the course',
            nullable: true
        ),
        new OA\Property(
            property: 'title',
            description: 'Title of the course',
            type: 'string',
            example: 'Math 101'
        ),
        new OA\Property(
            property: 'slug',
            description: 'Slug of the course',
            type: 'string',
            example: 'math-101'
        ),
        new OA\Property(
            property: 'description',
            description: 'Description of the course',
            type: 'string',
            example: 'A basic mathematics course',
            nullable: true
        ),
        new OA\Property(
            property: 'type',
            description: 'Type of the course',
            type: 'string',
            enum: [CourseType::ONLINE->value, CourseType::OFFLINE->value, CourseType::VIDEO->value],
            example: CourseType::ONLINE->value
        ),
        new OA\Property(
            property: 'price',
            description: 'Price of the course',
            type: 'string',
            format: 'float',
            example: 99.99
        ),
        new OA\Property(
            property: 'image_url',
            description: 'Image url of the course',
            type: 'string',
            format: 'url',
            example: 'http://loclhost:8080/storage/courses/course1.png',
            nullable: true
        ),
        new OA\Property(
            property: 'is_published',
            description: 'Status of the course',
            type: 'boolean',
            example: true
        ),
        new OA\Property(
            property: 'created_at',
            description: 'Creation date of the course',
            type: 'string',
            format: 'date-time',
            example: '2026-01-20T12:00:00Z'
        ),
        new OA\Property(
            property: 'updated_at',
            description: 'Modification date of the course',
            type: 'string',
            format: 'date-time',
            example: '2026-01-25T12:00:00Z'
        ),
        new OA\Property(
            property: 'lessons_count',
            description: 'Total number of course lessons',
            type: 'integer',
            example: 10,
            nullable: true
        )
    ],
    type: 'object'
)]
class CourseResponseSchema
{
}
