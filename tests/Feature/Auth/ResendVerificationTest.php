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

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('sends verification email if email is unverified', function () {
            $user = User::factory()
                ->unverified()
                ->create();

            Sanctum::actingAs($user);

            postJson(route('auth.verification.resend'))
                ->assertNoContent();

            Notification::assertSentTo($user, QueuedVerifyEmailNotification::class);
        });

        it('does nothing if email is already verified', function () {
            $user = User::factory()
                ->verified()
                ->create();
            Sanctum::actingAs($user);

            postJson(route('auth.verification.resend'))
                ->assertNoContent();

            Notification::assertNothingSent();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails for unauthenticated user', function () {
            postJson(route('auth.verification.resend'))
                ->assertUnauthorized();
        });
    });
})->group('auth');
