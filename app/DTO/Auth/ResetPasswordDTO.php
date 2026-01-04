<?php

namespace App\DTO\Auth;

use Illuminate\Http\Request;

class ResetPasswordDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $resetToken,

    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            resetToken: $request->string('resetToken')->toString(),
        );
    }
}
