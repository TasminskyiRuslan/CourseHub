<?php

namespace App\DTO\Auth;

use App\Enums\UserRole;
use App\Http\Requests\RegisterRequest;

class RegisterDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public UserRole $role,
        public string $password,
        public bool $remember
    ) {}

    public static function fromRequest(RegisterRequest $request): self
    {
        return new self(
            $request->name,
            $request->email,
            UserRole::from($request->role),
            $request->password,
            $request->boolean("remember", false),
        );
    }
}
