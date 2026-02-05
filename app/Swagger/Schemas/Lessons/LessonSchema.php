<?php

namespace App\Swagger\Schemas\Lessons;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Lesson',
    title: 'Lesson schema',
    description: 'Details of a lesson returned by the API',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            ref: '#/components/schemas/LessonData'
        )
    ],
    type: 'object'
)]
class LessonSchema
{
}
