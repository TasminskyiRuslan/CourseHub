<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('ResetPasswordController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'password' => 'old-password',
        ]);

        $this->token = Password::createToken($this->user);

        $this->payload = [
            'email' => $this->user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'token' => $this->token,
        ];
    });

    describe('success', function () {
        it('resets the password', function () {
            postJson(route('auth.password.reset'), $this->payload)
                ->assertNoContent();

            $this->user->refresh();
            expect(Hash::check('new-password', $this->user->password))->toBeTrue();
        });
    });

    describe('validation', function () {
        it('fails when token is invalid', function () {
            postJson(route('auth.password.reset'), array_merge($this->payload, [
                'token' => 'invalid-token',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        it('fails when required fields are missing', function () {
            postJson(route('auth.password.reset'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email', 'password', 'token']);
        });

        it('fails when password confirmation does not match', function () {
            postJson(route('auth.password.reset'), array_merge($this->payload, [
                'password_confirmation' => 'different',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });

        it('fails when email format is invalid', function () {
            postJson(route('auth.password.reset'), array_merge($this->payload, [
                'email' => 'invalid-email',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails when email does not exist', function () {
            postJson(route('auth.password.reset'), array_merge($this->payload, [
                'email' => 'nonexistent@example.com',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails when the new password is too short', function () {
            postJson(route('auth.password.reset'), array_merge($this->payload, [
                'password' => '123',
                'password_confirmation' => '123',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });
    });

    describe('permissions', function () {
        beforeEach(function () {
            $this->admin = User::factory()->admin()->create();

            $this->payload = [
                'email' => $this->admin->email,
                'password' => 'new-admin-password',
                'password_confirmation' => 'new-admin-password',
                'token' => 'dummy-token',
            ];
        });

        it('forbids admin', function () {
            postJson(route('auth.password.reset'), $this->payload)
                ->assertForbidden();
        });
    });
})->group('auth');
