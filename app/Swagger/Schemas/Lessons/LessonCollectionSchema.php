<?php

namespace App\Swagger\Schemas\Lessons;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LessonCollection',
    title: 'Lesson collection',
    description: 'Collection of lessons',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/LessonData')
        )
    ],
    type: 'object'
)]
class LessonCollectionSchema
{
}
