<?php

namespace App\Swagger\Requests\Lessons\Store;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StoreLessonBaseRequest',
    title: 'Store Lesson Base Request',
    required: ['title'],
    properties: [
        new OA\Property(
            property: 'title',
            type: 'string',
            maxLength: 255,
            example: 'Introduction to Algebra'
        ),
        new OA\Property(
            property: 'slug',
            type: 'string',
            maxLength: 255,
            example: 'introduction-to-algebra',
            nullable: true
        ),
        new OA\Property(
            property: 'position',
            type: 'integer',
            minimum: 0,
            example: 1,
            nullable: true
        ),
    ],
    type: 'object'
)]
class StoreLessonBaseRequestSchema
{
}
