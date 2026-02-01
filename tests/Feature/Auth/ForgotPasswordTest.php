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
    });

    it('sends a password reset link to an existing user', function () {
        postJson('/api/auth/password/forgot', ['email' => $this->user->email])
            ->assertNoContent();
        Notification::assertSentTo($this->user, QueuedResetPasswordNotification::class, function ($notification) {
            return !empty($notification->token);
        });
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $this->user->email,
        ]);
    });

    it('fails when the email field is empty', function () {
        postJson('/api/auth/password/forgot', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    });

    it('fails when the email is invalid', function () {
        postJson('/api/auth/password/forgot', ['email' => 'not-an-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    });

    it('does not send an email for non-existent users', function () {
        postJson('/api/auth/password/forgot', ['email' => 'non-existent@example.com'])
            ->assertNoContent();
        Notification::assertNothingSent();
    });
});
