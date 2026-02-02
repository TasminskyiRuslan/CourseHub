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

        $this->payload = fn(array $overrides = []) => array_merge([
            'email' => $this->user->email,
        ], $overrides);
    });

    describe('password reset request', function () {
        it('sends a reset link to an existing user', function () {
            postJson(route('auth.password.forgot'), ($this->payload)())
                ->assertNoContent();

            Notification::assertSentTo(
                $this->user,
                QueuedResetPasswordNotification::class,
                fn ($notification) => !empty($notification->token)
            );

            $this->assertDatabaseHas('password_reset_tokens', [
                'email' => $this->user->email,
            ]);
        });

        it('does not send a reset link when the email does not exist', function () {
            $email = 'non-existent@example.com';

            postJson(route('auth.password.forgot'), ($this->payload)(['email' => $email]))
                ->assertNoContent();

            Notification::assertNothingSent();

            $this->assertDatabaseMissing('password_reset_tokens', [
                'email' => $email,
            ]);
        });
    });

    describe('validation', function () {
        it('fails when the email is missing', function () {
            postJson(route('auth.password.forgot'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        it('fails when the email format is invalid', function () {
            postJson(route('auth.password.forgot'), ($this->payload)(['email' => 'invalid-email']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });
    });
});
