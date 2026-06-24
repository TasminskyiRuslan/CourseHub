<?php

namespace App\Swagger\Account\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateAccountAvatarRequest',
    title: 'Update Account Avatar Request',
    description: 'Request payload for updating a user account avatar.',
    required: ['image'],
    properties: [
        new OA\Property(
            property: 'image',
            description: 'Avatar of the user account.',
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
class UpdateAccountAvatarRequestSchema
{

}
