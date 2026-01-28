<?php

namespace App\Swagger\Requests\Courses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateCourseImageRequest',
    title: 'Update course image request schema',
    description: 'Schema for updating a course image. Supports common image formats up to 2MB',
    required: ['image'],
    properties: [
        new OA\Property(
            property: 'image',
            description: 'The image file. Allowed formats: jpg, jpeg, png, webp. Max size: 2048 KB.',
            type: 'string',
            format: 'binary'
        )
    ],
    type: 'object'
)]
class UpdateCourseImageRequestSchema
{
}
