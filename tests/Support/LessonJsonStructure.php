<?php

namespace Tests\Support;

class LessonJsonStructure
{
    public static function get(): array
    {
        return [
            'id',
            'course_id',
            'title',
            'slug',
            'position',
            'content' => [],
            'created_at',
            'updated_at',
        ];
    }
}
