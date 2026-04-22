<?php

namespace App\Swagger\Lesson\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoLessonResponse',
    title: 'Video Lesson Response',
    description: 'Content of a specific video lesson.',
    required: ['video_url', 'provider'],
    properties: [
        new OA\Property(
            property: 'video_url',
            description: 'Link of the video lesson.',
            type: 'string',
            format: 'uri',
            example: 'https://videos.example.com/lesson123.mp4',
            nullable: true
        ),
        new OA\Property(
            property: 'provider',
            description: 'Provider of the video lesson.',
            type: 'string',
            example: 'youtube',
            nullable: true
        ),
    ],
    type: 'object'
)]
class VideoLessonResponseSchema
{

}
