<?php

use App\Models\User;
use Carbon\Carbon;
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

        $this->makePayload = fn(array $overrides = []) => array_merge([
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
            $data = ($this->makePayload)();

            postJson(route('auth.login'), $data)
                ->assertOk()
                ->assertJsonPath('data.user.email', $data['email'])
                ->assertJsonStructure([
                    'data' => AuthJsonStructure::get(),
                ]);
        });

        it('sets a long token expiration when remember is true', function () {
            Carbon::setTestNow(now());
            $data = ($this->makePayload)(['remember' => true]);

            $response = postJson(route('auth.login'), $data)
                ->assertOk();

            $expiresAt = Carbon::parse($response->json('data.expires_at'));
            expect($expiresAt->greaterThan(Carbon::now()->copy()->addWeek()))->toBeTrue();

            Carbon::setTestNow();
        });

        it('sets a short token expiration when remember is false', function () {
            Carbon::setTestNow(now());
            $data = ($this->makePayload)(['remember' => false]);

            $response = postJson(route('auth.login'), $data)
                ->assertOk();

            $expiresAt = Carbon::parse($response->json('data.expires_at'));
            expect($expiresAt->lessThanOrEqualTo(Carbon::now()->copy()->addDay()))->toBeTrue();

            Carbon::setTestNow();
        });

        it('sets a short token expiration by default', function () {
            Carbon::setTestNow(now());
            $data = ($this->makePayload)();

            $response = postJson(route('auth.login'), $data)
                ->assertOk();

            $expiresAt = Carbon::parse($response->json('data.expires_at'));
            expect($expiresAt->lessThanOrEqualTo(Carbon::now()->copy()->addDay()))->toBeTrue();

            Carbon::setTestNow();
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
            $data = ($this->makePayload)(['email' => 'nonexistent@example.com']);

            postJson(route('auth.login'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        it('fails when email format is invalid', function () {
            $data = ($this->makePayload)(['email' => 'invalid-email']);

            postJson(route('auth.login'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        it('fails when credentials are incorrect', function () {
            $data = ($this->makePayload)(['password' => 'wrong-password']);

            postJson(route('auth.login'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });
    });
})->group('auth');
