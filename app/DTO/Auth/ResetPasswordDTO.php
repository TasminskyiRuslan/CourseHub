<?php

namespace App\DTO\Auth;

use App\Http\Requests\Api\Auth\ResetPasswordRequest;

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
            email: $request->input('email'),
            password: $request->input('password'),
            token: $request->input('token'),
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
