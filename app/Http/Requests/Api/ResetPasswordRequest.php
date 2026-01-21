<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('email')) {
            $data['email'] = $this->string('email')->trim()->toString();
        }

        if ($this->has('token')) {
            $data['token'] = $this->string('token')->trim()->toString();
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'token' => ['required', 'string'],
        ];
    }
}
