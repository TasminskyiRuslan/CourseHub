<?php

namespace App\Swagger\Lesson\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateOnlineLessonRequest',
    title: 'Update Online Lesson Request',
    description: 'Online part of request payload for updating the lesson.',
    properties: [
        new OA\Property(
            property: 'start_time',
            description: 'Start time of the online lesson.',
            type: 'string',
            format: 'date-time',
            example: '2027-02-02T10:00:00Z',
            nullable: true
        ),
        new OA\Property(
            property: 'end_time',
            description: 'End time of the online lesson.',
            type: 'string',
            format: 'date-time',
            example: '2027-02-02T12:00:00Z',
            nullable: true
        ),
        new OA\Property(
            property: 'meeting_link',
            description: 'Meeting link of the lesson.',
            type: 'string',
            format: 'uri',
            maxLength: 2048,
            example: 'https://meet.example.com/lesson123',
            nullable: true
        ),
    ],
    type: 'object'
)]
class UpdateOnlineLessonRequestSchema
{
}
