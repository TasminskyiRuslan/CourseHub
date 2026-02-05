<?php

namespace App\Swagger\Requests\Lessons\Store;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StoreOfflineLessonRequest',
    title: 'Store Offline Lesson Request',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/StoreLessonBaseRequest'),
        new OA\Schema(
            required: ['start_time', 'end_time', 'address', 'room_number'],
            properties: [
                new OA\Property(
                    property: 'start_time',
                    type: 'string',
                    format: 'date-time',
                    example: '2027-02-01T10:00:00Z'
                ),
                new OA\Property(
                    property: 'end_time',
                    type: 'string',
                    format: 'date-time',
                    example: '2027-02-01T12:00:00Z'
                ),
                new OA\Property(
                    property: 'address',
                    type: 'string',
                    maxLength: 255,
                    example: '123 Main St, Springfield'
                ),
                new OA\Property(
                    property: 'room_number',
                    type: 'string',
                    maxLength: 50,
                    example: 'Room 101'
                ),
            ],
            type: 'object'
        )
    ]
)]
class StoreOfflineLessonRequestSchema
{
}
