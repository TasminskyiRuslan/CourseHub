<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\Auth\PasswordResetNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('Auth -> SendPasswordResetLinkController', function () {
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
            postJson(route('auth.password.forgot'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the email format is invalid', function () {
            postJson(route('auth.password.forgot'), [
                'email' => 'invalid-email',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the email is too long', function () {
            postJson(route('auth.password.forgot'), [
                'email' => str_repeat('a', 256).'@example.com',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('sends a password reset link to a valid user', function (?User $user) {
            Notification::fake();

            postJson(route('auth.password.forgot'), [
                'email' => $user->email,
            ])->assertNoContent();

            Notification::assertSentTo(
                $user,
                PasswordResetNotification::class,
                fn ($notification) => ! empty($notification->token)
            );

            $this->assertDatabaseHas('password_reset_tokens', [
                'email' => $user->email,
            ]);
        })
            ->with([
                'verified user' => fn () => User::factory()->create(),
                'unverified user' => fn () => User::factory()->unverified()->create(),
            ]);

        it('sends a password reset link when email is provided in uppercase', function () {
            Notification::fake();

            $user = User::factory()->create(['email' => 'user@example.com']);

            postJson(route('auth.password.forgot'), [
                'email' => 'USER@EXAMPLE.COM',
            ])->assertNoContent();

            Notification::assertSentTo(
                $user,
                PasswordResetNotification::class,
                fn ($notification) => ! empty($notification->token)
            );

            $this->assertDatabaseHas('password_reset_tokens', [
                'email' => 'user@example.com',
            ]);
        });

        it('does not send a notification if the email does not exist', function () {
            Notification::fake();

            postJson(route('auth.password.forgot'), [
                'email' => 'nonexistent@example.com',
            ])->assertNoContent();

            Notification::assertNothingSent();

            $this->assertDatabaseCount('password_reset_tokens', 0);
        });
    });
})->group('auth');
