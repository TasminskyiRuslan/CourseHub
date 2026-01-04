<?php

namespace App\DTO\Auth;

use App\Enums\UserRole;
use Illuminate\Http\Request;

readonly class RegisterDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public UserRole $role = UserRole::STUDENT,
        public bool $remember = false,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            role: $request->filled('role') ? UserRole::from($request->string('role')->toString()) : UserRole::STUDENT,
            remember: $request->boolean('remember'),
        );
    }
}
