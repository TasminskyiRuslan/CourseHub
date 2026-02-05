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

        $this->user = User::factory()->verified()->create(['password' => $this->oldPassword]);
        $this->admin = User::factory()->admin()->create(['password' => $this->oldPassword]);

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
            postJson(route('auth.password.reset'), ($this->makePayload)())
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
        postJson(route('auth.password.reset'), ($this->makePayload)(['token' => 'invalid-token']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        it('fails when required fields are missing', function () {
            postJson(route('auth.password.reset'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email', 'password', 'token']);
        });

        it('fails when password confirmation does not match', function () {
            postJson(route('auth.password.reset'), ($this->makePayload)(['password_confirmation' => 'different']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });

        it('fails when email format is invalid', function () {
            postJson(route('auth.password.reset'), ($this->makePayload)(['email' => 'invalid-email']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails when email does not exist', function () {
            postJson(route('auth.password.reset'), ($this->makePayload)(['email' => 'nonexistent@example.com']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails when the new password is too short', function () {
            postJson(route('auth.password.reset'), ($this->makePayload)([
                'password' => '123',
                'password_confirmation' => '123',
            ]))
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
            postJson(route('auth.password.reset'), ($this->makePayload)([
                'email' => $this->admin->email,
                'token' => $this->adminToken,
            ]))
                ->assertForbidden();
        });
    });
})->group('auth');
