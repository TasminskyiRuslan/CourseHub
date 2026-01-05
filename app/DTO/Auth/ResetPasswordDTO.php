<?php

namespace App\DTO\Auth;

use App\Http\Requests\Api\ResetPasswordRequest;

readonly class ResetPasswordDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $token,

    ) {}

    public static function fromRequest(ResetPasswordRequest $request): self
    {
        $validated = $request->safe();

        return new self(
            email: $validated->email,
            password: $validated->password,
            token: $validated->reset_token,
        );
    }
}
