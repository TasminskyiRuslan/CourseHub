<?php

namespace App\Swagger\Requests\Lessons\Update;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateLessonRequest',
    title: 'Update Lesson Request',
    description: 'Schema for updating a lesson depending on course type',
    oneOf: [
        new OA\Schema(ref: '#/components/schemas/UpdateOfflineLessonRequest'),
        new OA\Schema(ref: '#/components/schemas/UpdateOnlineLessonRequest'),
        new OA\Schema(ref: '#/components/schemas/UpdateVideoLessonRequest'),
    ]
)]
class UpdateLessonRequestSchema {}
