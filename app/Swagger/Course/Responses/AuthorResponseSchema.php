<?php

namespace App\Swagger\Course\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Author',
    title: 'Author Schema',
    description: 'Details of a author returned by the API',
    required: ['id', 'name', 'slug'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'Unique identifier of the user.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'name',
            description: 'Name of the user.',
            type: 'string',
            example: 'John Doe'
        ),
        new OA\Property(
            property: 'slug',
            description: 'Slug of the user.',
            type: 'string',
            example: 'john-doe'
        ),
    ],
    type: 'object'
)]
class AuthorResponseSchema
{
}
