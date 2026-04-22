<?php

namespace App\Swagger\Lesson\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OnlineLessonResponse',
    title: 'Online Lesson Response',
    description: 'Content of a specific online lesson.',
    required: ['start_time', 'end_time', 'meeting_link'],
    properties: [
        new OA\Property(
            property: 'start_time',
            description: 'Start time of the online lesson.',
            type: 'string',
            format: 'date-time',
            example: '2027-02-01T10:00:00Z',
            nullable: true
        ),
        new OA\Property(
            property: 'end_time',
            description: 'End time of the online lesson.',
            type: 'string',
            format: 'date-time',
            example: '2027-02-01T12:00:00Z',
            nullable: true
        ),
        new OA\Property(
            property: 'meeting_link',
            description: 'Link of the online lesson.',
            type: 'string',
            format: 'uri',
            example: 'https://meet.example.com/lesson123',
            nullable: true
        ),
    ],
    type: 'object'
)]
class OnlineLessonResponseSchema
{

}
