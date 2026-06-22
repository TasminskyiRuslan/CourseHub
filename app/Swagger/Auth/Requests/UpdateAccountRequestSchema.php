<?php

namespace App\Swagger\Auth\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateAccountRequest',
    title: 'Update Account Request',
    description: 'Request payload for updating an account.',
    required: ['name', 'slug'],
    properties: [
        new OA\Property(
            property: 'name',
            description: 'Full name of the user.',
            type: 'string',
            maxLength: 100,
            minLength: 2,
            example: 'John Doe'
        ),
        new OA\Property(
            property: 'slug',
            description: 'Slug of the user. (optional, must be unique if provided)',
            type: 'string',
            maxLength: 100,
            pattern: '^[a-z0-9-]+$',
            example: 'john-doe'
        ),
    ],
    type: 'object'
)]
class UpdateAccountRequestSchema
{
}
