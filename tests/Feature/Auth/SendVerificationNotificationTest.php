<?php

use App\Models\User;
use App\Notifications\Auth\EmailVerificationNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('Auth -> SendEmailVerificationNotificationController', function () {
    beforeEach(function () {
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | validations
    |--------------------------------------------------------------------------
    */
    describe('validations', function () {
        it('fails if email is already verified', function () {
            Notification::fake();
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            postJson(route('auth.verification.send'))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);

            Notification::assertNothingSent();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to request a verification link', function () {
            postJson(route('auth.verification.send'))
                ->assertUnauthorized();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('sends a verification email if the user\'s email is unverified', function () {
            Notification::fake();
            $user = User::factory()->unverified()->create();
            Sanctum::actingAs($user);

            postJson(route('auth.verification.send'))
                ->assertNoContent();
            Notification::assertSentTo($user, EmailVerificationNotification::class);
        });
    });
})->group('auth');
