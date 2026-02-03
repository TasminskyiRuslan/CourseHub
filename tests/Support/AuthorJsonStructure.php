<?php

namespace Tests\Support;

class AuthorJsonStructure
{
    public static function get(): array
    {
        return [
            'id',
            'name',
            'slug',
        ];
    }
}
