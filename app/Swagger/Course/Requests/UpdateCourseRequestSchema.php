<?php

namespace App\Swagger\Course\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateCourseRequest',
    title: 'Update course request schema',
    description: 'Schema for updating an existing course via API request',
    required: ['title', 'slug', 'description', 'price'],
    properties: [
        new OA\Property(
            property: 'title',
            description: 'The title of the course',
            type: 'string',
            maxLength: 255,
            example: 'Math 101'
        ),
        new OA\Property(
            property: 'slug',
            description: 'The slug of the course',
            type: 'string',
            maxLength: 255,
            example: 'math-101'
        ),
        new OA\Property(
            property: 'description',
            description: 'The description of the course',
            type: 'string',
            example: 'A basic mathematics course',
            nullable: true
        ),
        new OA\Property(
            property: 'price',
            description: 'The price of the course',
            type: 'string',
            format: 'float',
            maximum: 99999999.99,
            minimum: 0,
            example: 199.99
        ),
    ],
    type: 'object'
)]
class UpdateCourseRequestSchema
{
}
