<?php

namespace App\Swagger\Schemas\Lessons;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Lesson',
    title: 'Lesson schema',
    description: 'Details of a lesson returned by the API',
    required: ['id', 'course_id', 'title', 'slug', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'The ID of the lesson',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'course_id',
            description: 'The ID of the course',
            type: 'integer',
            example: 10
        ),
        new OA\Property(
            property: 'title',
            description: 'The title of the lesson',
            type: 'string',
            example: 'Introduction to Algebra'
        ),
        new OA\Property(
            property: 'slug',
            description: 'The slug of the lesson',
            type: 'string',
            example: 'introduction-to-algebra'
        ),
        new OA\Property(
            property: 'position',
            description: 'The position of the lesson',
            type: 'integer',
            example: 1
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
            example: '123 Main St, Springfield',
            nullable: true
        ),
        new OA\Property(
            property: 'room_number',
            description: 'The number of the lesson (offline)',
            type: 'string',
            example: 'Room 101',
            nullable: true
        ),
        new OA\Property(
            property: 'meeting_link',
            description: 'The link of the lesson (online)',
            type: 'string',
            format: 'uri',
            example: 'https://meet.example.com/lesson123',
            nullable: true
        ),
        new OA\Property(
            property: 'video_url',
            description: 'The video of the lesson (video)',
            type: 'string',
            format: 'uri',
            example: 'https://videos.example.com/lesson123.mp4',
            nullable: true
        ),
        new OA\Property(
            property: 'provider',
            description: 'The provider of the lesson (video)',
            type: 'string',
            example: 'YouTube',
            nullable: true
        ),
        new OA\Property(
            property: 'created_at',
            description: 'The creation date of the lesson',
            type: 'string',
            format: 'date-time',
            example: '2026-01-01T12:00:00Z'
        ),
        new OA\Property(
            property: 'updated_at',
            description: 'The modification date of the lesson',
            type: 'string',
            format: 'date-time',
            example: '2026-01-10T12:00:00Z'
        ),
    ],
    type: 'object'
)]
class LessonSchema {}
