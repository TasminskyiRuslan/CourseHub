<?php

namespace App\DTO\Auth;

use App\Http\Requests\ResetPasswordRequest;

class ResetPasswordDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $password_confirmation,
        public string $token,

    ) {}

    public static function fromRequest(ResetPasswordRequest $request): self
    {
        return new self(
            $request->email,
            $request->password,
            $request->password_confirmation,
            $request->token,
        );
    }
}
