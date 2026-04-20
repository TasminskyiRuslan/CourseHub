<?php

namespace App\Swagger\Course\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateCourseImageRequest',
    title: 'Update Course Image Request',
    description: 'Request payload for updating a course image.',
    required: ['image', '_method'],
    properties: [
        new OA\Property(
            property: 'image',
            description: 'Image of the course.',
            type: 'string',
            format: 'binary'
        ),
        new OA\Property(
            property: '_method',
            description: 'Method spoofing to treat POST as PUT.',
            type: 'string',
            default: 'PUT',
            enum: ['PUT'],
            example: 'PUT'
        )
    ],
    type: 'object'
)]
class UpdateCourseImageRequestSchema
{
}
