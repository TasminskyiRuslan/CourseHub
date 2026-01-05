<?php

namespace App\DTO\Auth;

use App\Enums\UserRole;
use App\Http\Requests\Api\RegisterRequest;

readonly class RegisterDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public UserRole $role,
        public bool     $remember,
    ) {}

    public static function fromRequest(RegisterRequest $request): self
    {
        $validated = $request->safe();

        return new self(
            name: $validated->name,
            email: $validated->email,
            password: $validated->password,
            role: isset($validated->role) ? UserRole::from($validated->role) : UserRole::STUDENT,
            remember: $validated->remember ?? false,
        );
    }
}
