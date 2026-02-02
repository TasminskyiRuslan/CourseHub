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
    });

    it('allows a user to login with correct credentials', function () {
        postJson(route('auth.login'), [
            'email' => $this->user->email,
            'password' => $this->password,
        ])
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

    it('sets a long expiration when remember is true', function () {
        $response = postJson(route('auth.login'), [
            'email' => $this->user->email,
            'password' => $this->password,
            'remember' => true,
        ])->assertOk();

        $expiresAt = strtotime($response->json('data.expires_at'));
        $expected = strtotime(now()->addWeeks(2));
        expect($expiresAt)->toBe($expected);
    });

    it('sets a short expiration when remember is false', function () {
        $response = postJson(route('auth.login'), [
            'email' => $this->user->email,
            'password' => $this->password,
            'remember' => false,
        ])->assertOk();

        $expiresAt = strtotime($response->json('data.expires_at'));
        $expected = strtotime(now()->addDay());
        expect($expiresAt)->toBe($expected);
    });

    it('sets a short expiration when remember is missing', function () {
        $response = postJson(route('auth.login'), [
            'email' => $this->user->email,
            'password' => $this->password,
        ])->assertOk();

        $expiresAt = strtotime($response->json('data.expires_at'));
        $expected = strtotime(now()->addDay());
        expect($expiresAt)->toBe($expected);
    });

    it('fails when credentials are missing', function () {
        postJson(route('auth.login'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    });

    it('fails when email is invalid', function () {
        postJson(route('auth.login'), [
            'email' => 'invalid-email',
            'password' => $this->password,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    });

    it('fails with incorrect password', function () {
        postJson(route('auth.login'), [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    });

    it('fails with non-existent email', function () {
        postJson(route('auth.login'), [
            'email' => 'non-existent@example.com',
            'password' => $this->password,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    });
});
