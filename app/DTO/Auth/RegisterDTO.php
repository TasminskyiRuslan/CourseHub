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
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password'),
            role: $request->enum('role', UserRole::class),
            remember: $request->input('remember'),
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
