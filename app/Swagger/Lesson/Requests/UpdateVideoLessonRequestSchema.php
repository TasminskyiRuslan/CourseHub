<?php

namespace App\Swagger\Lesson\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateVideoLessonRequest',
    title: 'Update Video Lesson Request',
    description: 'Video part of payload for updating the lesson.',
    properties: [
        new OA\Property(
            property: 'video_url',
            description: 'Video URL.',
            type: 'string',
            format: 'uri',
            maxLength: 2048,
            example: 'https://vimeo.com/lesson123',
            nullable: true
        ),
        new OA\Property(
            property: 'provider',
            description: 'Video provider.',
            type: 'string',
            maxLength: 50,
            example: 'vimeo',
            nullable: true
        )
    ],
    type: 'object'
)]
class UpdateVideoLessonRequestSchema
{
}
