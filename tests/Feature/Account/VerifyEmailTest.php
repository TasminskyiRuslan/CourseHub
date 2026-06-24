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

describe('VerifyEmailController', function () {

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
            $user = User::factory()->student()->unverified()->create();

            getJson(URL::temporarySignedRoute(
                'account.verification.verify',
                now()->addMinutes(60),
                [
                    'id' => 999999,
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            ))
                ->assertForbidden();
        });

        it('fails when the hash is incorrect', function () {
            $user = User::factory()->student()->unverified()->create();

            getJson(URL::temporarySignedRoute(
                'account.verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->id,
                    'hash' => 'wrong-hash',
                ]
            ))
                ->assertForbidden();
        });

        it('fails when the signature is missing', function () {
            $user = User::factory()->student()->unverified()->create();

            getJson(route('account.verification.verify', [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('verifies the email for user', function ($user) {
            Event::fake();
            getJson(URL::temporarySignedRoute(
                'account.verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->id,
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            ))
                ->assertNoContent();

            expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
            Event::assertDispatched(Verified::class, fn($event) => $event->user->id === $user->id);
        })
            ->with([
                'student' => fn() => User::factory()->student()->unverified()->create(),
                'teacher' => fn() => User::factory()->teacher()->unverified()->create(),
                'admin' => fn() => User::factory()->admin()->unverified()->create(),
            ]);

        it('does nothing if the email is already verified', function () {
            Event::fake();
            $user = User::factory()->create();

            getJson(URL::temporarySignedRoute(
                'account.verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->id,
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            ))->assertNoContent();
            Event::assertNotDispatched(Verified::class);
        });
    });
})->group('account');
