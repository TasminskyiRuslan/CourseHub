<?php

namespace App\Swagger\Schemas\Courses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CourseCollection',
    title: 'Course collection',
    description: 'Collection of courses',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/CourseData')
        )
    ],
    type: 'object'
)]
class CourseCollectionSchema
{
}
