<?php

namespace App\Swagger\Course\Requests;

use App\Enums\CourseType;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StoreCourseRequest',
    title: 'Store Course Request',
    description: 'Request payload for creating a new course.',
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
            pattern: '^[a-z0-9-]+$',
            example: 'math-101',
            nullable: true
        ),
        new OA\Property(
            property: 'description',
            description: 'The description of the new course',
            type: 'string',
            maxLength: 5000,
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
            example: CourseType::ONLINE->value,
            nullable: false
        ),
        new OA\Property(
            property: 'price',
            description: 'The price of the new course',
            type: 'string',
            format: 'float',
            maximum: 99999999.99,
            minimum: 0,
            example: 199.99
        ),
    ],
    type: 'object'
)]
class StoreCourseRequestSchema
{
}
