<?php

namespace App\Swagger\Course\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateCourseImageRequest',
    title: 'Update Course Image Request',
    description: 'Multipart form data payload for uploading a new image via PUT spoofing.',
    required: ['image', '_method'],
    properties: [
        new OA\Property(
            property: 'image',
            description: 'Image file.',
            type: 'string',
            format: 'binary'
        ),
        new OA\Property(
            property: '_method',
            description: 'Method spoofing to handle multipart/form-data in PUT requests.',
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
