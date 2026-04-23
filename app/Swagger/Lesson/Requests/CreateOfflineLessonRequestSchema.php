<?php

namespace App\Swagger\Lesson\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateOfflineLessonRequest',
    title: 'Create Offline Lesson Request',
    description: 'Offline part of request payload for creating a new lesson.',
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
            maxLength: 255,
            example: '123 Main St, Springfield',
            nullable: true
        ),
        new OA\Property(
            property: 'room_number',
            description: 'Room number of the offline lesson.',
            type: 'string',
            maxLength: 50,
            example: 'Room 101',
            nullable: true
        ),
    ],
    type: 'object'
)]
class CreateOfflineLessonRequestSchema
{
}
