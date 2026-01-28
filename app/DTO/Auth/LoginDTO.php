<?php

namespace App\DTO\Auth;

use App\Http\Requests\Api\Auth\LoginRequest;

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
            email: $request->input('email'),
            password: $request->input('password'),
            remember: $request->boolean('remember'),
        );
    }

    public function toArray(): array
    {
        return [
            'email'    => $this->email,
            'password' => $this->password,
            'remember' => $this->remember,
        ];
    }
}
