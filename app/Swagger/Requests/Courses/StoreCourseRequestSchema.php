<?php

namespace App\Swagger\Requests\Courses;

use App\Enums\CourseType;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StoreCourseRequest',
    title: 'Store course request schema',
    description: 'Schema for creating a new course via API request',
    required: ['title', 'type', 'price'],
    properties: [
        new OA\Property(
            property: 'title',
            description: 'The title of the new course',
            type: 'string',
            maxLength: 255,
            example: 'Math 101'
        ),
        new OA\Property(
            property: 'slug',
            description: 'The slug of the new course',
            type: 'string',
            maxLength: 255,
            example: 'math-101',
            nullable: true
        ),
        new OA\Property(
            property: 'description',
            description: 'The description of the new course',
            type: 'string',
            example: 'A basic mathematics course',
            nullable: true
        ),
        new OA\Property(
            property: 'type',
            description: 'The type of the new course',
            type: 'string',
            enum: [
                CourseType::OFFLINE->value,
                CourseType::ONLINE->value,
                CourseType::VIDEO->value,
            ],
            example: CourseType::ONLINE->value
        ),
        new OA\Property(
            property: 'price',
            description: 'The price of the new course',
            type: 'number',
            format: 'double',
            maximum: 99999999.99,
            minimum: 0,
            example: 199.99
        ),
    ],
    type: 'object'
)]
class StoreCourseRequestSchema {}
