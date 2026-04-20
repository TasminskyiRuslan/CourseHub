<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('ResetPasswordController', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the token is invalid', function () {
            $newPassword = 'new-password';
            $user = User::factory()->create();

            postJson(route('auth.password.reset'), [
                'email' => $user->email,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => 'invalid-token',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        it('fails if the required fields are missing', function () {
            postJson(route('auth.password.reset'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email', 'password', 'token']);
        });

        it('fails if the password confirmation does not match', function () {
            $user = User::factory()->create();

            postJson(route('auth.password.reset'), [
                'email' => $user->email,
                'password' => 'new-password',
                'password_confirmation' => 'different',
                'token' => Password::createToken($user),
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });

        it('fails if the email format is invalid', function () {
            $newPassword = 'new-password';
            $user = User::factory()->create();

            postJson(route('auth.password.reset'), [
                'email' => 'invalid-email',
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => Password::createToken($user),
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the email does not exist', function () {
            $newPassword = 'new-password';
            $user = User::factory()->create();

            postJson(route('auth.password.reset'), [
                'email' => 'nonexistent@example.com',
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => Password::createToken($user),
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the new password is too short', function () {
            $user = User::factory()->create();

            postJson(route('auth.password.reset'), [
                'email' => $user->email,
                'password' => '123',
                'password_confirmation' => '123',
                'token' => Password::createToken($user),
            ])
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
        it('allows users to reset password', function ($user) {
            $newPassword = 'new-password';

            postJson(route('auth.password.reset'), [
                'email' => $user->email,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => Password::createToken($user),
            ])
                ->assertNoContent();
            $user->refresh();
            expect(Hash::check($newPassword, $user->password))->toBeTrue();
        })
            ->with([
                'student' => fn() => User::factory()->student()->create(),
                'teacher' => fn() => User::factory()->teacher()->create(),
                'admin' => fn() => User::factory()->admin()->create(),
            ]);

        it('fails if a super-admin tries to reset password', function () {
            $newPassword = 'new-password';
            $superAdmin = User::whereEmail(config('super-admin.email'))->first();

            postJson(route('auth.password.reset'), [
                'email' => $superAdmin->email,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => Password::createToken($superAdmin),
            ])
                ->assertNoContent();

            $superAdmin->refresh();
            expect(Hash::check(config('super-admin.password'), $superAdmin->password))->toBeTrue()
                ->and(Hash::check($newPassword, $superAdmin->password))->toBeFalse();
        });
    });
})->group('auth');
