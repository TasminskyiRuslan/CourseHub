<?php

namespace App\Swagger\Account\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateUserAccountRequest',
    title: 'Update User Account Request',
    description: 'Request payload for updating a user account.',
    required: ['name', 'slug'],
    properties: [
        new OA\Property(
            property: 'name',
            description: 'Full name of the user account.',
            type: 'string',
            maxLength: 100,
            minLength: 2,
            example: 'John Doe'
        ),
        new OA\Property(
            property: 'slug',
            description: 'Slug of the user account. (optional, must be unique if provided)',
            type: 'string',
            maxLength: 100,
            pattern: '^[a-z0-9-]+$',
            example: 'john-doe'
        ),
    ],
    type: 'object'
)]
class UpdateUserAccountRequestSchema
{
}
