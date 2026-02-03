<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('VerifyEmailController', function () {
    beforeEach(function () {
        Event::fake();

        $this->user = User::factory()
            ->unverified()
            ->create();

        $this->signedUrl = fn(array $overrides = []) => URL::temporarySignedRoute(
            'auth.verification.verify',
            now()->addMinutes(60),
            array_merge([
                'id' => $this->user->id,
                'hash' => sha1($this->user->getEmailForVerification()),
            ], $overrides)
        );
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('verifies the email', function () {
            getJson(($this->signedUrl)())
                ->assertNoContent();

            expect($this->user->fresh()->hasVerifiedEmail())->toBeTrue();
        });

        it('dispatches the verified event', function () {
            getJson(($this->signedUrl)())
                ->assertNoContent();

            Event::assertDispatched(Verified::class, function ($event) {
                return $event->user->id === $this->user->id;
            });
        });

        it('does nothing if the email is already verified', function () {
            $this->user->markEmailAsVerified();

            getJson(($this->signedUrl)())
                ->assertNoContent();

            Event::assertNotDispatched(Verified::class);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails when the user ID does not exist', function () {
            getJson(($this->signedUrl)(['id' => 99999]))
                ->assertForbidden();
        });

        it('fails when the hash is incorrect', function () {
            getJson(($this->signedUrl)(['hash' => 'wrong-hash']))
                ->assertForbidden();
        });

        it('fails when the signature is missing', function () {
            $url = route('auth.verification.verify', [
                'id' => $this->user->id,
                'hash' => sha1($this->user->getEmailForVerification()),
            ]);

            getJson($url)
                ->assertForbidden();
        });
    });
})->group('auth');
