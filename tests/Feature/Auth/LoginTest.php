<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('LoginController', function () {
    beforeEach(function () {
        $this->password = 'password';

        $this->user = User::factory()->create([
            'password' => $this->password,
        ]);

        $this->payload = fn(array $overrides = []) => array_merge([
            'email' => $this->user->email,
            'password' => $this->password,
        ], $overrides);
    });

    describe('authentication process', function () {

        it('authenticates the user successfully', function () {
            postJson(route('auth.login'), ($this->payload)())
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'slug',
                            'email',
                            'role',
                            'email_verified_at',
                            'created_at',
                            'updated_at',
                        ],
                        'access_token',
                        'token_type',
                        'expires_at',
                    ]
                ]);
        });

        it('sets a long token expiration when remember is true', function () {
            $response = postJson(route('auth.login'), ($this->payload)(['remember' => true]))
                ->assertOk();

            $expiresAt = strtotime($response->json('data.expires_at'));
            $expected = strtotime(now()->addWeeks(2));
            expect($expiresAt)->toBe($expected);
        });

        it('sets a short token expiration when remember is false', function () {
            $response = postJson(route('auth.login'), ($this->payload)(['remember' => false]))
                ->assertOk();

            $expiresAt = strtotime($response->json('data.expires_at'));
            $expected = strtotime(now()->addDay());
            expect($expiresAt)->toBe($expected);
        });

        it('sets a short token expiration by default', function () {
            $response = postJson(route('auth.login'), ($this->payload)())
                ->assertOk();

            $expiresAt = strtotime($response->json('data.expires_at'));
            $expected = strtotime(now()->addDay());
            expect($expiresAt)->toBe($expected);
        });
    });

    describe('validation', function () {
        it('fails when credentials are missing', function () {
            postJson(route('auth.login'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email', 'password']);
        });

        it('fails when the email format is invalid', function () {
            postJson(route('auth.login'), ($this->payload)(['email' => 'invalid-email']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        it('fails when the password is incorrect', function () {
            postJson(route('auth.login'), ($this->payload)(['password' => 'wrong-password']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        it('fails when the email does not exist', function () {
            postJson(route('auth.login'), ($this->payload)(['email' => 'non-existent@example.com']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });
    });
});
