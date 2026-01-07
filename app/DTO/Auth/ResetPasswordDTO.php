<?php

namespace App\DTO\Auth;

use App\Http\Requests\Api\ResetPasswordRequest;

final readonly class ResetPasswordDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $token,
    ) {}

    public static function fromRequest(ResetPasswordRequest $request): self
    {
        return new self(
            email: $request->string('email')->trim(),
            password: $request->string('password'),
            token: $request->string('token'),
        );
    }

    public function toArray(): array
    {
        return [
            'email'    => $this->email,
            'password' => $this->password,
            'token'    => $this->token,
        ];
    }
}
