<?php

namespace Tests\Support;

class UserJsonStructure
{
    public static function get(): array
    {
        return [
            'id',
            'name',
            'slug',
            'email',
            'role',
            'email_verified_at',
            'created_at',
            'updated_at',
        ];
    }
}
