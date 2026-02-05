<?php

namespace App\Swagger\Requests\Lessons\Store;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StoreLessonRequest',
    title: 'Store Lesson Request',
    description: 'Schema for creating a lesson depending on course type',
    oneOf: [
        new OA\Schema(ref: '#/components/schemas/StoreOfflineLessonRequest'),
        new OA\Schema(ref: '#/components/schemas/StoreOnlineLessonRequest'),
        new OA\Schema(ref: '#/components/schemas/StoreVideoLessonRequest'),
    ]
)]
class StoreLessonRequestSchema
{
}
