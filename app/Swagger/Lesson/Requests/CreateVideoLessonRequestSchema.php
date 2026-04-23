<?php

namespace App\Swagger\Lesson\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateVideoLessonRequest',
    title: 'Create Video Lesson Request',
    description: 'Video part of request payload for creating a new lesson.',
    properties: [
        new OA\Property(
            property: 'video_url',
            description: 'Link of the video lesson.',
            type: 'string',
            format: 'uri',
            maxLength: 2048,
            example: 'https://videos.example.com/lesson123.mp4',
            nullable: true,
        ),
        new OA\Property(
            property: 'provider',
            description: 'Provider of the video lesson.',
            type: 'string',
            maxLength: 50,
            example: 'youtube',
            nullable: true
        ),
    ],
    type: 'object'
)]
class CreateVideoLessonRequestSchema
{
}
