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

        $this->makeSignedUrl = fn(array $overrides = []) => URL::temporarySignedRoute(
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
            $url = ($this->makeSignedUrl)();

            getJson($url)->assertNoContent();

            expect($this->user->fresh()->hasVerifiedEmail())->toBeTrue();
        });

        it('dispatches the verified event', function () {
            $url = ($this->makeSignedUrl)();

            getJson($url)->assertNoContent();

            Event::assertDispatched(Verified::class, fn($event) => $event->user->id === $this->user->id);
        });

        it('does nothing if the email is already verified', function () {
            $this->user->markEmailAsVerified();

            $url = ($this->makeSignedUrl)();

            getJson($url)->assertNoContent();

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
            $url = ($this->makeSignedUrl)(['id' => 99999]);

            getJson($url)->assertForbidden();
        });

        it('fails when the hash is incorrect', function () {
            $url = ($this->makeSignedUrl)(['hash' => 'wrong-hash']);

            getJson($url)->assertForbidden();
        });

        it('fails when the signature is missing', function () {
            $url = route('auth.verification.verify', [
                'id' => $this->user->id,
                'hash' => sha1($this->user->getEmailForVerification()),
            ]);

            getJson($url)->assertForbidden();
        });
    });

})->group('auth');
