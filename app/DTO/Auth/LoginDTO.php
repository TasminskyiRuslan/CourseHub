<?php

namespace App\DTO\Auth;

use App\Http\Requests\Api\LoginRequest;

readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember,
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        $validated = $request->safe();

        return new self(
            email: $validated->email,
            password: $validated->password,
            remember: $validated->remember ?? false,
        );
    }
}
