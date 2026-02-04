<?php

namespace Tests\Support;

use App\Enums\CourseType;

class LessonJsonStructure
{
    public static function get(CourseType $type): array
    {
        return [
            'id',
            'course_id',
            'title',
            'slug',
            'position',
            'content' => match ($type) {
                CourseType::OFFLINE => [
                    'start_time',
                    'end_time',
                    'address',
                    'room_number'
                ],
                CourseType::ONLINE => [
                    'start_time',
                    'end_time',
                    'meeting_link',
                ],
                CourseType::VIDEO => [
                    'video_url',
                    'provider',
                ],
            },
            'created_at',
            'updated_at',
        ];
    }
}
