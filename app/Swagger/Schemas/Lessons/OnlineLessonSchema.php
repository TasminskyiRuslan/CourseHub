<?php

namespace App\Swagger\Schemas\Lessons;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OnlineLesson',
    title: 'Online lesson schema',
    description: 'Online content of a lesson returned by the API',
    required: ['start_time', 'end_time', 'meeting_link'],
    properties: [
        new OA\Property(
            property: 'start_time',
            description: 'The start time of the lesson',
            type: 'string',
            format: 'date-time',
            example: '2027-02-01T10:00:00Z'
        ),
        new OA\Property(
            property: 'end_time',
            description: 'The end time of the lesson',
            type: 'string',
            format: 'date-time',
            example: '2027-02-01T12:00:00Z'
        ),
        new OA\Property(
            property: 'meeting_link',
            description: 'The link of the lesson',
            type: 'string',
            format: 'uri',
            example: 'https://meet.example.com/lesson123'
        ),
    ],
    type: 'object'
)]
class OnlineLessonSchema {}
