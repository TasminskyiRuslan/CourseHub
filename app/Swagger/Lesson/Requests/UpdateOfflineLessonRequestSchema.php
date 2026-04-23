<?php

namespace App\Swagger\Lesson\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateOfflineLessonRequest',
    title: 'Update Offline Lesson Request',
    description: 'Offline part of request payload for updating the lesson.',
    properties: [
        new OA\Property(
            property: 'start_time',
            description: 'Start time of the offline lesson.',
            type: 'string',
            format: 'date-time',
            example: '2027-02-02T10:00:00Z',
            nullable: true
        ),
        new OA\Property(
            property: 'end_time',
            description: 'End time of the offline lesson.',
            type: 'string',
            format: 'date-time',
            example: '2027-02-02T12:00:00Z',
            nullable: true
        ),
        new OA\Property(
            property: 'address',
            description: 'Address of the offline lesson.',
            type: 'string',
            maxLength: 255,
            example: '125 Main St, Springfield',
            nullable: true
        ),
        new OA\Property(
            property: 'room_number',
            description: 'Room number of the offline lesson.',
            type: 'string',
            maxLength: 50,
            example: 'Room 105',
            nullable: true
        ),
    ],
    type: 'object'
)]
class UpdateOfflineLessonRequestSchema
{
}
