<?php

namespace App\Swagger\Requests\Lessons;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateLessonRequest',
    title: 'Update Lesson Request Schema',
    description: 'Schema for updating an existing lesson via API request',
    required: ['title', 'slug'],
    properties: [
        new OA\Property(
            property: 'title',
            description: 'The title of the lesson',
            type: 'string',
            maxLength: 255,
            example: 'Introduction to Algebra'
        ),
        new OA\Property(
            property: 'slug',
            description: 'The slug of the lesson',
            type: 'string',
            maxLength: 255,
            example: 'introduction-to-algebra'
        ),
        new OA\Property(
            property: 'position',
            description: 'The position of the lesson',
            type: 'integer',
            minimum: 0,
            example: 1,
            nullable: true
        ),
        new OA\Property(
            property: 'start_time',
            description: 'The start time of the lesson (offline, online)',
            type: 'string',
            format: 'date-time',
            example: '2027-02-01T10:00:00Z',
            nullable: true
        ),
        new OA\Property(
            property: 'end_time',
            description: 'The end time of the lesson (offline, online)',
            type: 'string',
            format: 'date-time',
            example: '2027-02-01T12:00:00Z',
            nullable: true
        ),
        new OA\Property(
            property: 'address',
            description: 'The address of the lesson (offline)',
            type: 'string',
            maxLength: 255,
            example: '123 Main St, Springfield',
            nullable: true
        ),
        new OA\Property(
            property: 'room_number',
            description: 'The room number of the lesson (offline)',
            type: 'string',
            maxLength: 50,
            example: 'Room 101',
            nullable: true
        ),
        new OA\Property(
            property: 'meeting_link',
            description: 'The link of the lesson (online)',
            type: 'string',
            format: 'uri',
            maxLength: 2048,
            example: 'https://meet.example.com/lesson123',
            nullable: true
        ),
        new OA\Property(
            property: 'video_url',
            description: 'The video of the lesson (video)',
            type: 'string',
            format: 'uri',
            maxLength: 2048,
            example: 'https://videos.example.com/lesson123.mp4',
            nullable: true
        ),
        new OA\Property(
            property: 'provider',
            description: 'The provider of the lesson (video)',
            type: 'string',
            maxLength: 50,
            example: 'YouTube',
            nullable: true
        ),
    ],
    type: 'object'
)]
class UpdateLessonRequestSchema {}
