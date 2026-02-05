<?php

namespace App\Swagger\Requests\Lessons\Update;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateVideoLessonRequest',
    title: 'Update Video Lesson Request',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/UpdateLessonBaseRequest'),
        new OA\Schema(
            required: ['video_url', 'provider'],
            properties: [
                new OA\Property(
                    property: 'video_url',
                    type: 'string',
                    format: 'uri',
                    maxLength: 2048,
                    example: 'https://videos.example.com/lesson123.mp4'
                ),
                new OA\Property(
                    property: 'provider',
                    type: 'string',
                    maxLength: 50,
                    example: 'youtube'
                ),
            ],
            type: 'object'
        )
    ]
)]
class UpdateVideoLessonRequestSchema
{
}
