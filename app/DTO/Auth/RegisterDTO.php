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
            name: $request->string('name')->trim(),
            email: $request->string('email')->trim(),
            password: $request->string('password'),
            role: $request->enum('role', UserRole::class) ?? UserRole::STUDENT,
            remember: $request->boolean('remember'),
        );
    }

    public function toArray(): array
    {
        return [
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => $this->password,
            'role'     => $this->role->value,
            'remember' => $this->remember,
        ];
    }
}
