<?php

namespace App\Swagger\Schemas\Courses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Course',
    title: 'Course schema',
    description: 'Details of a course returned by the API',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            ref: '#/components/schemas/CourseData'
        )
    ],
    type: 'object'
)]
class CourseSchema
{
}
