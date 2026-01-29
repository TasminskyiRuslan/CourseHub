<?php

namespace App\Swagger\Schemas\Lessons;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OfflineLesson',
    title: 'Offline lesson schema',
    description: 'Offline content of a lesson returned by the API',
    required: ['start_time', 'end_time', 'address', 'room_number'],
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
            property: 'address',
            description: 'The address of the lesson',
            type: 'string',
            example: '123 Main St, Springfield'
        ),
        new OA\Property(
            property: 'room_number',
            description: 'The room number of the lesson',
            type: 'string',
            example: 'Room 101'
        ),
    ],
    type: 'object'
)]
class OfflineLessonSchema {}
