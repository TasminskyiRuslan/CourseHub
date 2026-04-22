<?php

namespace App\Swagger\Lesson\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OfflineLessonResponse',
    title: 'Offline Lesson Response',
    description: 'Content of a specific offline lesson.',
    required: ['start_time', 'end_time', 'address', 'room_number'],
    properties: [
        new OA\Property(
            property: 'start_time',
            description: 'Start time of the offline lesson.',
            type: 'string',
            format: 'date-time',
            example: '2027-02-01T10:00:00Z',
            nullable: true
        ),
        new OA\Property(
            property: 'end_time',
            description: 'End time of the offline lesson.',
            type: 'string',
            format: 'date-time',
            example: '2027-02-01T12:00:00Z',
            nullable: true
        ),
        new OA\Property(
            property: 'address',
            description: 'Address of the offline lesson.',
            type: 'string',
            example: '123 Main St, Springfield',
            nullable: true
        ),
        new OA\Property(
            property: 'room_number',
            description: 'Room number of the offline lesson.',
            type: 'string',
            example: 'Room 101',
            nullable: true
        )
    ],
    type: 'object'
)]
class OfflineLessonResponseSchema
{

}
