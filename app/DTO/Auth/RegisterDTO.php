<?php

namespace App\DTO\Auth;

use App\Enums\UserRole;
use App\Http\Requests\Api\RegisterRequest;

final readonly class RegisterDTO
{
    public function __construct(
        public string   $name,
        public string   $email,
        public string   $password,
        public UserRole $role,
        public bool     $remember,
    ) {}

    public static function fromRequest(RegisterRequest $request): self
    {
        return new self(
            name: $request->string('name')->trim()->toString(),
            email: $request->string('email')->trim()->toString(),
            password: $request->string('password')->toString(),
            role: $request->enum('role', UserRole::class) ?? UserRole::STUDENT,
            remember: $request->boolean('remember'),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => $this->password,
            'role'     => $this->role->value,
            'remember' => $this->remember,
        ], fn($value) => !is_null($value));
    }
}
