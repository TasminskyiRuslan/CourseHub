<?php

namespace App\DTO\Auth;

use App\Http\Requests\Api\LoginRequest;

final readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public bool   $remember,
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        return new self(
            email: $request->string('email')->trim()->toString(),
            password: $request->string('password')->toString(),
            remember: $request->boolean('remember'),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'email'    => $this->email,
            'password' => $this->password,
            'remember' => $this->remember,
        ], fn($value) => !is_null($value));
    }
}
