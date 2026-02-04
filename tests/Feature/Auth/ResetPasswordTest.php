<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('ResetPasswordController', function () {

    beforeEach(function () {
        $this->oldPassword = 'old-password';
        $this->newPassword = 'new-password';

        $this->user = User::factory()
            ->verified()
            ->create([
                'password' => $this->oldPassword,
            ]);

        $this->admin = User::factory()
            ->admin()
            ->create([
                'password' => $this->oldPassword,
            ]);

        $this->userToken = Password::createToken($this->user);
        $this->adminToken = Password::createToken($this->admin);

        $this->makePayload = fn(array $overrides = []) => array_merge([
            'email' => $this->user->email,
            'password' => $this->newPassword,
            'password_confirmation' => $this->newPassword,
            'token' => $this->userToken,
        ], $overrides);
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('resets the password', function () {
            $data = ($this->makePayload)();

            postJson(route('auth.password.reset'), $data)
                ->assertNoContent();

            $this->user->refresh();
            expect(Hash::check($this->newPassword, $this->user->password))->toBeTrue();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {

        it('fails when token is invalid', function () {
            $data = ($this->makePayload)(['token' => 'invalid-token']);

            postJson(route('auth.password.reset'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        it('fails when required fields are missing', function () {
            postJson(route('auth.password.reset'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email', 'password', 'token']);
        });

        it('fails when password confirmation does not match', function () {
            $data = ($this->makePayload)(['password_confirmation' => 'different']);

            postJson(route('auth.password.reset'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });

        it('fails when email format is invalid', function () {
            $data = ($this->makePayload)(['email' => 'invalid-email']);

            postJson(route('auth.password.reset'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails when email does not exist', function () {
            $data = ($this->makePayload)(['email' => 'nonexistent@example.com']);

            postJson(route('auth.password.reset'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails when the new password is too short', function () {
            $data = ($this->makePayload)([
                'password' => '123',
                'password_confirmation' => '123',
            ]);

            postJson(route('auth.password.reset'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('forbids admin', function () {
            $data = ($this->makePayload)([
                'email' => $this->admin->email,
                'token' => $this->adminToken,
            ]);

            postJson(route('auth.password.reset'), $data)
                ->assertForbidden();
        });
    });

})->group('auth');
