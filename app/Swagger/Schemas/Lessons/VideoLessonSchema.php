<?php

namespace App\Swagger\Schemas\Lessons;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoLesson',
    title: 'Video lesson schema',
    description: 'Video content of a lesson returned by the API',
    required: ['video_url', 'provider'],
    properties: [
        new OA\Property(
            property: 'video_url',
            description: 'The link of the video',
            type: 'string',
            format: 'uri',
            example: 'https://videos.example.com/lesson123.mp4'
        ),
        new OA\Property(
            property: 'provider',
            description: 'The provider of the video',
            type: 'string',
            example: 'youtube'
        ),
    ],
    type: 'object'
)]
class VideoLessonSchema {}
