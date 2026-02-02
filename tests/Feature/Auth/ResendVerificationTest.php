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

    describe('when authenticated', function () {

        it('sends a verification email when the email is unverified', function () {
            $user = User::factory()->unverified()->create();
            Sanctum::actingAs($user);

            postJson(route('auth.verification.resend'))
                ->assertNoContent();

            Notification::assertSentTo($user, QueuedVerifyEmailNotification::class);
        });

        it('does not send a verification email when the email is already verified', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            postJson(route('auth.verification.resend'))
                ->assertNoContent();

            Notification::assertNothingSent();
        });
    });

    describe('when not authenticated', function () {
        it('fails when the user is not authenticated', function () {
            postJson(route('auth.verification.resend'))
                ->assertUnauthorized();
        });
    });
});
