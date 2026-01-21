<?php

namespace App\Http\Requests\Api;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('name')) {
            $data['name'] = $this->string('name')->trim()->toString();
        }

        if ($this->has('email')) {
            $data['email'] = $this->string('email')->trim()->toString();
        }

        if ($this->has('role')) {
            $data['role'] = $this->string('role')->trim()->toString();
        }

        if ($this->has('remember')) {
            $data['remember'] = $this->boolean('remember');
        }

        $this->merge($data);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'role' => [
                'required',
                Rule::enum(UserRole::class)->only([UserRole::STUDENT, UserRole::TEACHER]),
            ],
            'remember' => ['required', 'boolean'],
        ];
    }
}
