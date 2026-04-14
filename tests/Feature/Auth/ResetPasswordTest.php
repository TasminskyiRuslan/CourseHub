<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('ResetPasswordController', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the token is invalid', function () {
            $newPassword = 'new-password';
            $user = User::factory()->verified()->create();

            postJson(route('password.reset'), [
                'email' => $user->email,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => 'invalid-token',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        it('fails if the required fields are missing', function () {
            postJson(route('password.reset'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email', 'password', 'token']);
        });

        it('fails if the password confirmation does not match', function () {
            $user = User::factory()->verified()->create();

            postJson(route('password.reset'), [
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
            $user = User::factory()->verified()->create();

            postJson(route('password.reset'), [
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
            $user = User::factory()->verified()->create();

            postJson(route('password.reset'), [
                'email' => 'nonexistent@example.com',
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => Password::createToken($user),
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the new password is too short', function () {
            $user = User::factory()->verified()->create();

            postJson(route('password.reset'), [
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
        it('allows a student to reset password', function () {
            $newPassword = 'new-password';
            $student = User::factory()->student()->create();

            postJson(route('password.reset'), [
                'email' => $student->email,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => Password::createToken($student),
            ])
                ->assertNoContent();
            $student->refresh();
            expect(Hash::check($newPassword, $student->password))->toBeTrue();
        });

        it('allows a teacher to reset password', function () {
            $newPassword = 'new-password';
            $teacher = User::factory()->teacher()->create();

            postJson(route('password.reset'), [
                'email' => $teacher->email,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => Password::createToken($teacher),
            ])
                ->assertNoContent();
            $teacher->refresh();
            expect(Hash::check($newPassword, $teacher->password))->toBeTrue();
        });

        it('allows an admin to reset password', function () {
            $newPassword = 'new-password';
            $admin = User::factory()->admin()->create();

            postJson(route('password.reset'), [
                'email' => $admin->email,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => Password::createToken($admin),
            ])
                ->assertNoContent();
            $admin->refresh();
            expect(Hash::check($newPassword, $admin->password))->toBeTrue();
        });

        it('fails if a super-admin tries to reset password', function () {
            $newPassword = 'new-password';
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);

            postJson(route('password.reset'), [
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
