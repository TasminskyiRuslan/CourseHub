<?php

namespace App\Swagger\Course\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateCourseImageRequest',
    title: 'Update Course Image Request',
    description: 'Request payload for updating a course image.',
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
