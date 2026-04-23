<?php

namespace App\Swagger\Course\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateCourseRequest',
    title: 'Update Course Request',
    description: 'Request payload for updating the course.',
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
            description: 'Slug of the course. (must be unique)',
            type: 'string',
            maxLength: 255,
            pattern: '^[a-z0-9-]+$',
            example: 'math-101'
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
class UpdateCourseRequestSchema
{
}
