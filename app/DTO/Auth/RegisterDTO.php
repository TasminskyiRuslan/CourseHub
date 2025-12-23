<?php

namespace App\DTO\Auth;

use App\Http\Requests\RegisterRequest;

class RegisterDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public bool $remember
    ) {}

    public static function fromRequest(RegisterRequest $request): self
    {
        return new self(
            $request->name,
            $request->email,
            $request->password,
            $request->boolean("remember", false),
        );
    }
}
