<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('Auth -> VerifyEmailController', function () {

    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->withoutMiddleware(ThrottleRequests::class);
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the user ID does not exist', function () {
            $user = User::factory()->unverified()->create();

            $url = URL::temporarySignedRoute(
                'auth.verification.verify',
                now()->addMinutes(60),
                [
                    'id' => 999999,
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            );

            getJson($url)->assertForbidden();
        });

        it('fails if the hash is incorrect', function () {
            $user = User::factory()->unverified()->create();

            $url = URL::temporarySignedRoute(
                'auth.verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->id,
                    'hash' => 'wrong-hash',
                ]
            );

            getJson($url)->assertForbidden();
        });

        it('fails if the signature is missing', function () {
            $user = User::factory()->unverified()->create();

            getJson(route('auth.verification.verify', [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]))->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('verifies the email address for a user', function ($user) {
            Event::fake();

            $url = URL::temporarySignedRoute(
                'auth.verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->id,
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            );

            getJson($url)->assertNoContent();

            expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
            Event::assertDispatched(Verified::class, fn($event) => $event->user->id === $user->id);
        })
            ->with([
                'user' => fn() => User::factory()->unverified()->create(),
                'teacher' => fn() => User::factory()->teacher()->unverified()->create(),
                'admin' => fn() => User::factory()->admin()->unverified()->create(),
            ]);

        it('does nothing if the email is already verified', function () {
            Event::fake();
            $user = User::factory()->create();

            $url = URL::temporarySignedRoute(
                'auth.verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->id,
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            );

            getJson($url)->assertNoContent();
            Event::assertNotDispatched(Verified::class);
        });
    });
})->group('auth');
