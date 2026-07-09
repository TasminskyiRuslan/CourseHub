<?php

namespace App\Swagger\Lesson\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateOnlineLessonRequest',
    title: 'Create Online Lesson Request',
    description: 'Online part of payload for creating a new lesson.',
    properties: [
        new OA\Property(
            property: 'start_time',
            description: 'Start time.',
            type: 'string',
            format: 'date-time',
            example: '2026-02-01T10:00:00Z',
            nullable: true
        ),
        new OA\Property(
            property: 'end_time',
            description: 'End time.',
            type: 'string',
            format: 'date-time',
            example: '2026-02-01T12:00:00Z',
            nullable: true
        ),
        new OA\Property(
            property: 'meeting_link',
            description: 'Meeting link.',
            type: 'string',
            format: 'uri',
            maxLength: 2048,
            example: 'https://meet.example.com/lesson123',
            nullable: true
        )
    ],
    type: 'object'
)]
class CreateOnlineLessonRequestSchema
{
}
