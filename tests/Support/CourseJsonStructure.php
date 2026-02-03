<?php

namespace Tests\Support;

class CourseJsonStructure
{
    public static function get(): array
    {
        return [
            'id',
            'author_id',
            'author' => AuthorJsonStructure::get(),
            'title',
            'slug',
            'description',
            'type',
            'price',
            'image_url',
            'is_published',
            'created_at',
            'updated_at',
        ];
    }
}
