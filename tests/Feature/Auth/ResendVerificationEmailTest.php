<?php

use App\Models\User;
use App\Notifications\QueuedVerifyEmailNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('ResendVerificationEmailController', function () {
    beforeEach(function () {
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->seed(RolesAndPermissionsSeeder::class);
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

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('sends verification email if email is unverified', function () {
            Notification::fake();
            $user = User::factory()->unverified()->create();
            Sanctum::actingAs($user);

            postJson(route('auth.verification.resend'))
                ->assertNoContent();
            Notification::assertSentTo($user, QueuedVerifyEmailNotification::class);
        });

        it('does nothing if email is already verified', function () {
            Notification::fake();
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            postJson(route('auth.verification.resend'))
                ->assertNoContent();
            Notification::assertNothingSent();
        });
    });
})->group('auth');
