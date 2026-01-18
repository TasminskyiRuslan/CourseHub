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
            email: $request->string('email')->trim()->toString(),
            password: $request->string('password')->toString(),
            token: $request->string('token')->toString(),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'email'    => $this->email,
            'password' => $this->password,
            'token'    => $this->token,
        ], fn($value) => !is_null($value));
    }
}
