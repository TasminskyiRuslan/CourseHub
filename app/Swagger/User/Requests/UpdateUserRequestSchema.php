<?php

namespace App\Swagger\User\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateUserAccountRequest',
    title: 'Update User Account Request',
    description: 'Payload for updating user account profile details.',
    properties: [
        new OA\Property(
            property: 'name',
            description: 'Full name.',
            type: 'string',
            maxLength: 100,
            minLength: 2,
            example: 'John Doe'
        ),
        new OA\Property(
            property: 'slug',
            description: 'Unique auth slug.',
            type: 'string',
            maxLength: 100,
            pattern: '^[a-z0-9-]+$',
            example: 'john-doe'
        ),
    ],
    type: 'object'
)]
class UpdateUserRequestSchema
{
}
