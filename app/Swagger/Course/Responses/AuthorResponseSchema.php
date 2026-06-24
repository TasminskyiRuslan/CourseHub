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
        new OA\Property(
            property: 'avatar_url',
            description: 'Avatar url of the user.',
            type: 'string',
            format: 'uri',
            example: 'http://loclhost:8080/storage/users/user1.png',
            nullable: true
        )
    ],
    type: 'object'
)]
class AuthorResponseSchema
{
}
