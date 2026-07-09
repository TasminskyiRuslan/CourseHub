<?php

namespace App\Swagger\Lesson\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateVideoLessonRequest',
    title: 'Create Video Lesson Request',
    description: 'Video part of payload for creating a new lesson.',
    properties: [
        new OA\Property(
            property: 'video_url',
            description: 'Video URL.',
            type: 'string',
            format: 'uri',
            maxLength: 2048,
            example: 'https://youtube.com/lesson123',
            nullable: true
        ),
        new OA\Property(
            property: 'provider',
            description: 'Video provider.',
            type: 'string',
            maxLength: 50,
            example: 'youtube',
            nullable: true
        )
    ],
    type: 'object'
)]
class CreateVideoLessonRequestSchema
{
}
