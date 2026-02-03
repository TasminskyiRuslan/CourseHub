<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Support\AuthJsonStructure;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('RegisterController', function () {
    beforeEach(function () {
        $this->name = 'John Doe';
        $this->email = 'john@example.com';
        $this->password = 'password';

        $this->payload = fn(array $overrides = []) => array_merge([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password,
            'role' => UserRole::STUDENT->value,
        ], $overrides);
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('registers a user', function () {
            postJson(route('auth.register'), ($this->payload)())
                ->assertCreated()
                ->assertJsonStructure([
                    'data' => AuthJsonStructure::get(),
                ]);

            expect(User::where('email', $this->email)->exists())->toBeTrue();
        });

        it('dispatches the registered event', function () {
            Event::fake();

            postJson(route('auth.register'), ($this->payload)())
                ->assertCreated();

            Event::assertDispatched(Registered::class, fn($event) => $event->user->email === $this->email
            );
        });

        it('sets a long token expiration when remember is true', function () {
            $response = postJson(
                route('auth.register'),
                ($this->payload)(['remember' => true])
            )->assertCreated();

            $expiresAt = now()->parse($response->json('data.expires_at'));
            expect($expiresAt->greaterThan(now()->addWeek()))->toBeTrue();
        });

        it('sets a short token expiration when remember is false', function () {
            $response = postJson(
                route('auth.register'),
                ($this->payload)(['remember' => false])
            )->assertCreated();

            $expiresAt = now()->parse($response->json('data.expires_at'));
            expect($expiresAt->lessThanOrEqualTo(now()->addDay()))->toBeTrue();
        });

        it('sets a short token expiration by default', function () {
            $response = postJson(route('auth.register'), ($this->payload)())
                ->assertCreated();

            $expiresAt = now()->parse($response->json('data.expires_at'));
            expect($expiresAt->lessThanOrEqualTo(now()->addDay()))->toBeTrue();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails when required fields are missing', function () {
            postJson(route('auth.register'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
        });

        it('fails when the email is already taken', function () {
            User::factory()
                ->verified()
                ->create(['email' => $this->email]);

            postJson(route('auth.register'), ($this->payload)())
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails when the email format is invalid', function () {
            postJson(
                route('auth.register'),
                ($this->payload)(['email' => 'invalid-email'])
            )
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails when the password is too short', function () {
            postJson(route('auth.register'), ($this->payload)([
                'password' => '123',
                'password_confirmation' => '123',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });

        it('fails when the password confirmation does not match', function () {
            postJson(route('auth.register'), ($this->payload)([
                'password_confirmation' => 'different',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });

        it('fails when the role is invalid', function () {
            postJson(route('auth.register'), ($this->payload)(['role' => 'invalid-role']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['role']);
        });

        it('fails when attempting to register as an admin', function () {
            postJson(route('auth.register'), ($this->payload)([
                'role' => UserRole::ADMIN->value,
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['role']);

            expect(User::where('role', UserRole::ADMIN)->count())->toBe(0);
        });
    });
})->group('auth');
