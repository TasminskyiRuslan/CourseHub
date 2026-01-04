<?php

namespace App\DTO\Auth;

use App\Http\Requests\Api\LoginRequest;

class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        return new self(
            $request->email,
            $request->password,
            $request->boolean("remember", false),
        );
    }
}
