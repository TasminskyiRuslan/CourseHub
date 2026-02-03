<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AuthJsonStructure;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('LoginController', function () {
    beforeEach(function () {
        $this->password = 'password';

        $this->user = User::factory()
            ->verified()
            ->create([
                'password' => $this->password,
            ]);

        $this->payload = fn(array $overrides = []) => array_merge([
            'email' => $this->user->email,
            'password' => $this->password,
        ], $overrides);
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('authenticates the user', function () {
            postJson(route('auth.login'), ($this->payload)())
                ->assertOk()
                ->assertJsonStructure([
                    'data' => AuthJsonStructure::get(),
                ]);
        });

        it('sets a long token expiration when remember is true', function () {
            $response = postJson(
                route('auth.login'),
                ($this->payload)(['remember' => true])
            )->assertOk();

            $expiresAt = now()->parse($response->json('data.expires_at'));
            expect($expiresAt->greaterThan(now()->addWeek()))
                ->toBeTrue();
        });

        it('sets a short token expiration when remember is false', function () {
            $response = postJson(
                route('auth.login'),
                ($this->payload)(['remember' => false])
            )->assertOk();

            $expiresAt = now()->parse($response->json('data.expires_at'));
            expect($expiresAt->lessThanOrEqualTo(now()->addDay()))
                ->toBeTrue();
        });

        it('sets a short token expiration by default', function () {
            $response = postJson(route('auth.login'), ($this->payload)())
                ->assertOk();

            $expiresAt = now()->parse($response->json('data.expires_at'));
            expect($expiresAt->lessThanOrEqualTo(now()->addDay()))
                ->toBeTrue();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails when required fields are missing', function () {
            postJson(route('auth.login'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email', 'password']);
        });

        it('fails when the email does not exist', function () {
            postJson(route('auth.login'), ($this->payload)([
                'email' => 'nonexistent@example.com',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        it('fails when email format is invalid', function () {
            postJson(
                route('auth.login'),
                ($this->payload)(['email' => 'invalid-email'])
            )
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        it('fails when credentials are incorrect', function () {
            postJson(
                route('auth.login'),
                ($this->payload)(['password' => 'wrong-password'])
            )
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });
    });
})->group('auth');
