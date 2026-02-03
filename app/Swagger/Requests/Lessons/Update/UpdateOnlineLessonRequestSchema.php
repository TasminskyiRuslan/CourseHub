<?php

namespace App\Swagger\Requests\Lessons\Update;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateOnlineLessonRequest',
    title: 'Update Online Lesson Request',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/UpdateLessonBaseRequest'),
        new OA\Schema(
            required: ['start_time', 'end_time', 'meeting_link'],
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
                    property: 'meeting_link',
                    type: 'string',
                    format: 'uri',
                    maxLength: 2048,
                    example: 'https://meet.example.com/lesson123'
                ),
            ],
            type: 'object'
        )
    ]
)]
class UpdateOnlineLessonRequestSchema {}
