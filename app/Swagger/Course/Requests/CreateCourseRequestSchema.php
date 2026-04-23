<?php

namespace App\Swagger\Course\Requests;

use App\Enums\CourseType;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateCourseRequest',
    title: 'Create Course Request',
    description: 'Request payload for creating a new course.',
    required: ['title', 'type', 'price'],
    properties: [
        new OA\Property(
            property: 'title',
            description: 'Title of the course.',
            type: 'string',
            maxLength: 255,
            example: 'Math 101'
        ),
        new OA\Property(
            property: 'slug',
            description: 'Slug of the course. (optional, must be unique if provided)',
            type: 'string',
            maxLength: 255,
            pattern: '^[a-z0-9-]+$',
            example: 'math-101',
            nullable: true
        ),
        new OA\Property(
            property: 'description',
            description: 'Description of the course.',
            type: 'string',
            maxLength: 5000,
            example: 'A basic mathematics course',
            nullable: true
        ),
        new OA\Property(
            property: 'type',
            description: 'Type of the course.',
            type: 'string',
            enum: [
                CourseType::OFFLINE->value,
                CourseType::ONLINE->value,
                CourseType::VIDEO->value,
            ],
            example: CourseType::OFFLINE->value
        ),
        new OA\Property(
            property: 'price',
            description: 'Price of the course.',
            type: 'string',
            format: 'float',
            maximum: 99999999.99,
            minimum: 0,
            example: 199.99
        )
    ],
    type: 'object'
)]
class CreateCourseRequestSchema
{
}
