<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('RegisterController', function () {
    beforeEach(function () {
        $this->payload = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::STUDENT->value,
        ];
    });

    it('registers a new user and returns auth data', function () {
        postJson(route('auth.register'), $this->payload)
            ->assertCreated()
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

        expect(User::where('email', $this->payload['email'])->exists())->toBeTrue();
    });

    it('fires Registered event when a user registers', function () {
        Event::fake();

        postJson(route('auth.register'), $this->payload)
            ->assertCreated();

        Event::assertDispatched(Registered::class, function ($event) {
            return $event->user->email === $this->payload['email'];
        });
    });

    it('sets a long expiration when remember is true', function () {
        $response = postJson(route('auth.register'),
            array_merge($this->payload, ['remember' => true]))
            ->assertCreated();

        $expiresAt = strtotime($response->json('data.expires_at'));
        $expected = strtotime(now()->addWeeks(2));
        expect($expiresAt)->toBe($expected);
    });

    it('sets a short expiration when remember is false', function () {
        $response = postJson(route('auth.register'),
            array_merge($this->payload, ['remember' => false]))
            ->assertCreated();

        $expiresAt = strtotime($response->json('data.expires_at'));
        $expected = strtotime(now()->addDay());
        expect($expiresAt)->toBeLessThanOrEqual($expected);
    });

    it('sets a short expiration when remember is missing', function () {
        $response = postJson(route('auth.register'), $this->payload)
            ->assertCreated();

        $expiresAt = strtotime($response->json('data.expires_at'));
        $expected = strtotime(now()->addDay());
        expect($expiresAt)->toBeLessThanOrEqual($expected);
    });

    it('fails when required fields are missing', function () {
        postJson(route('auth.register'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
    });

    it('fails when email is already taken', function () {
        User::factory()->create(['email' => $this->payload['email']]);

        postJson(route('auth.register'), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('fails when email is invalid', function () {
        postJson(route('auth.register'), array_merge($this->payload, ['email' => 'invalid-email']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('fails when password is too short', function () {
        postJson(route('auth.register'), array_merge($this->payload, ['password' => '123', 'password_confirmation' => '123']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });

    it('fails when password confirmation does not match', function () {
        postJson(route('auth.register'), array_merge($this->payload, ['password_confirmation' => 'different']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });

    it('fails when role is invalid', function () {
        postJson(route('auth.register'), array_merge($this->payload, ['role' => 'invalid-role']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    });

    it('does not allow to register with admin role', function () {
        postJson(route('auth.register'), array_merge($this->payload, ['role' => UserRole::ADMIN->value]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);

        expect(User::where('role', UserRole::ADMIN)->count())->toBe(0);
    });
});
