<?php

use App\Models\User;
use App\Notifications\QueuedVerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('ResendVerificationController', function () {
    beforeEach(function () {
        Notification::fake();
        $this->withoutMiddleware(ThrottleRequests::class);
    });

    it('sends verification email if user email is not verified', function () {
        $user = User::factory()->unverified()->create();

        Sanctum::actingAs($user);

        postJson(route('auth.verification.resend'))
            ->assertNoContent();

        Notification::assertSentTo(
            $user,
            QueuedVerifyEmailNotification::class
        );
    });

    it('does not send verification email if user is already verified', function () {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        postJson(route('auth.verification.resend'))
            ->assertNoContent();

        Notification::assertNothingSent();
    });

    it('returns unauthorized if user is not authenticated', function () {
        postJson(route('auth.verification.resend'))
            ->assertUnauthorized();
    });
});
