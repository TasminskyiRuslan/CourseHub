<?php

namespace Tests\Support;

class AuthJsonStructure
{
    public static function get(): array
    {
        return [
            'user' => UserJsonStructure::get(),
            'access_token',
            'token_type',
            'expires_at',
        ];
    }
}
