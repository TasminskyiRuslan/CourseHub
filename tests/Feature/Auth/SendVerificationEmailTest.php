<?php

use App\Models\User;
use App\Notifications\Auth\VerifyEmailNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('Auth -> SendVerificationEmailController', function () {
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
        it('fails for unauthenticated user', function () {
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
        it('sends verification email if email is unverified', function () {
            Notification::fake();
            $user = User::factory()->unverified()->create();
            Sanctum::actingAs($user);

            postJson(route('auth.verification.send'))
                ->assertNoContent();
            Notification::assertSentTo($user, VerifyEmailNotification::class);
        });
    });
})->group('auth');
