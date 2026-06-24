<?php

namespace App\Swagger\Account\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateUserAccountImageRequest',
    title: 'Update User Account Image Request',
    description: 'Request payload for updating a user account image.',
    required: ['image'],
    properties: [
        new OA\Property(
            property: 'image',
            description: 'Image of the user account.',
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
class UpdateUserAccountImageRequestSchema
{

}
