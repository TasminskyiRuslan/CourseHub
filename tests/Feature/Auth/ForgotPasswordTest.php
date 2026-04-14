<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\QueuedResetPasswordNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Notification;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('ForgotPasswordController', function () {
    beforeEach(function () {
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the required fields are missing', function () {
            postJson(route('password.forgot'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the email format is invalid', function () {
            postJson(route('password.forgot'), [
                'email' => 'invalid-email',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the email is too long', function () {
            postJson(route('password.forgot'), [
                'email' => str_repeat('a', 256) . '@example.com',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('sends a reset link to a verified user', function () {
            Notification::fake();
            $user = User::factory()->verified()->create();

            postJson(route('password.forgot'), [
                'email' => $user->email,
            ])->assertNoContent();

            Notification::assertSentTo(
                $user,
                QueuedResetPasswordNotification::class,
                fn($notification) => !empty($notification->token)
            );

            $this->assertDatabaseHas('password_reset_tokens', [
                'email' => $user->email,
            ]);
        });

        it('sends a reset link to an unverified user', function () {
            Notification::fake();
            $user = User::factory()->unverified()->create();

            postJson(route('password.forgot'), [
                'email' => $user->email,
            ])->assertNoContent();

            Notification::assertSentTo(
                $user,
                QueuedResetPasswordNotification::class,
                fn($notification) => !empty($notification->token)
            );

            $this->assertDatabaseHas('password_reset_tokens', [
                'email' => $user->email,
            ]);
        });

        it('silently succeeds if the role is super-admin', function () {
            Notification::fake();
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);

            postJson(route('password.forgot'), [
                'email' => $superAdmin->email,
            ])->assertNoContent();

            Notification::assertNothingSent();
            $this->assertDatabaseCount('password_reset_tokens', 0);
        });

        it('silently succeeds if the email does not exist', function () {
            Notification::fake();
            postJson(route('password.forgot'), [
                'email' => 'nonexistent@example.com',
            ])->assertNoContent();

            Notification::assertNothingSent();
            $this->assertDatabaseCount('password_reset_tokens', 0);
        });
    });
})->group('auth');
