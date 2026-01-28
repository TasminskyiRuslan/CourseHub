<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PaginationMeta',
    title: 'Pagination meta schema',
    description: 'Metadata about pagination',
    required: ['current_page', 'last_page', 'per_page', 'total'],
    properties: [
        new OA\Property(
            property: 'current_page',
            description: 'The current page',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'last_page',
            description: 'The last page',
            type: 'integer',
            example: 10
        ),
        new OA\Property(
            property: 'per_page',
            description: 'The number of items per page',
            type: 'integer',
            example: 15
        ),
        new OA\Property(
            property: 'total',
            description: 'The total number of items',
            type: 'integer',
            example: 150
        ),
    ],
    type: 'object'
)]
class PaginationMetaSchema
{
}
