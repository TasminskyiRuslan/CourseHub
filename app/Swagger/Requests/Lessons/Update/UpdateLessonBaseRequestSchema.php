<?php

namespace App\Swagger\Requests\Lessons\Update;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateLessonBaseRequest',
    title: 'Update Lesson Base Request',
    required: ['title', 'slug', 'position'],
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
            example: 'introduction-to-algebra'
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
class UpdateLessonBaseRequestSchema {}
