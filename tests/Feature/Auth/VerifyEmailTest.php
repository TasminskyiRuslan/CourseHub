<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('VerifyEmailController', function () {

    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the user ID does not exist', function () {
            $user = User::factory()->unverified()->create();

            getJson(URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => 999999,
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            ))
                ->assertForbidden();
        });

        it('fails when the hash is incorrect', function () {
            $user = User::factory()->unverified()->create();

            getJson(URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->id,
                    'hash' => 'wrong-hash',
                ]
            ))
                ->assertForbidden();
        });

        it('fails when the signature is missing', function () {
            $user = User::factory()->unverified()->create();

            getJson(route('verification.verify', [
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
        it('verifies the email for a student', function () {
            Event::fake();
            $student = User::factory()->unverified()->student()->create();
            getJson(URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $student->id,
                    'hash' => sha1($student->getEmailForVerification()),
                ]
            ))
                ->assertNoContent();

            expect($student->fresh()->hasVerifiedEmail())->toBeTrue();
            Event::assertDispatched(Verified::class, fn($event) => $event->user->id === $student->id);
        });

        it('verifies the email for a teacher', function () {
            Event::fake();
            $teacher = User::factory()->unverified()->teacher()->create();
            getJson(URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $teacher->id,
                    'hash' => sha1($teacher->getEmailForVerification()),
                ]
            ))
                ->assertNoContent();

            expect($teacher->fresh()->hasVerifiedEmail())->toBeTrue();
            Event::assertDispatched(Verified::class, fn($event) => $event->user->id === $teacher->id);
        });

        it('does nothing if the email is already verified', function () {
            Event::fake();
            $user = User::factory()->verified()->create();

            getJson(URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->id,
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            ))->assertNoContent();
            Event::assertNotDispatched(Verified::class);
        });
    });
})->group('auth');
