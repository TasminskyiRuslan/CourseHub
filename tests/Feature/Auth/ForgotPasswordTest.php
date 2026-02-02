<?php

use App\Models\User;
use App\Notifications\QueuedResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Notification;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('ForgotPasswordController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();

        Notification::fake();
        $this->withoutMiddleware(ThrottleRequests::class);

        $this->payload = fn (array $overrides = []) => array_merge([
            'email' => $this->user->email,
        ], $overrides);
    });

    describe('success', function () {
        it('sends a reset link to an existing user', function () {
            postJson(route('auth.password.forgot'), ($this->payload)())
                ->assertNoContent();

            Notification::assertSentTo(
                $this->user,
                QueuedResetPasswordNotification::class,
                fn ($notification) => ! empty($notification->token)
            );

            $this->assertDatabaseHas('password_reset_tokens', [
                'email' => $this->user->email,
            ]);
        });

        it('silently succeeds when the email does not exist', function () {
            postJson(
                route('auth.password.forgot'),
                ($this->payload)(['email' => 'nonexistent@example.com'])
            )->assertNoContent();

            Notification::assertNothingSent();

            $this->assertDatabaseCount('password_reset_tokens', 0);
        });
    });

    describe('validation', function () {
        it('fails when email is missing', function () {
            postJson(route('auth.password.forgot'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        it('fails when email format is invalid', function () {
            postJson(
                route('auth.password.forgot'),
                ($this->payload)(['email' => 'invalid-email'])
            )
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });
    });
})->group('auth');
