<?php

use App\Enums\UserRole;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Support\AuthJsonStructure;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('RegisterController', function () {
    beforeEach(function () {
        $this->makePayload = fn(array $overrides = []) => array_merge([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
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
            $data = ($this->makePayload)();

            postJson(route('auth.register'), $data)
                ->assertCreated()
                ->assertJsonPath('data.user.email', $data['email'])
                ->assertJsonStructure([
                    'data' => AuthJsonStructure::get(),
                ]);

            expect(User::where('email', $data['email'])->exists())->toBeTrue();
        });

        it('dispatches the registered event', function () {
            Event::fake();

            $data = ($this->makePayload)();

            postJson(route('auth.register'), $data)
                ->assertCreated();

            Event::assertDispatched(Registered::class, fn($event) => $event->user->email === $data['email']);
        });

        it('sets a long token expiration when remember is true', function () {
            Carbon::setTestNow(now());

            $response = postJson(route('auth.register'), ($this->makePayload)(['remember' => true]))
                ->assertCreated();

            $expiresAt = Carbon::parse($response->json('data.expires_at'));
            expect($expiresAt->greaterThan(Carbon::now()->copy()->addWeek()))->toBeTrue();

            Carbon::setTestNow();
        });

        it('sets a short token expiration when remember is false', function () {
            Carbon::setTestNow(now());

            $response = postJson(route('auth.register'), ($this->makePayload)(['remember' => false]))
                ->assertCreated();

            $expiresAt = Carbon::parse($response->json('data.expires_at'));
            expect($expiresAt->lessThanOrEqualTo(Carbon::now()->copy()->addDay()))->toBeTrue();

            Carbon::setTestNow();
        });

        it('sets a short token expiration by default', function () {
            Carbon::setTestNow(now());

            $response = postJson(route('auth.register'), ($this->makePayload)())
                ->assertCreated();

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
            postJson(route('auth.register'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
        });

        it('fails when the email is already taken', function () {
            $data = ($this->makePayload)();

            User::factory()->verified()->create(['email' => $data['email']]);

            postJson(route('auth.register'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails when the email format is invalid', function () {
            postJson(route('auth.register'), ($this->makePayload)(['email' => 'invalid-email']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails when the password is too short', function () {
            postJson(route('auth.register'), ($this->makePayload)([
                'password' => '123',
                'password_confirmation' => '123',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });

        it('fails when the password confirmation does not match', function () {
            postJson(route('auth.register'), ($this->makePayload)([
                'password_confirmation' => 'different',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });

        it('fails when the role is invalid', function () {
            postJson(route('auth.register'), ($this->makePayload)(['role' => 'invalid-role']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['role']);
        });

        it('fails when attempting to register as an admin', function () {
            postJson(route('auth.register'), ($this->makePayload)(['role' => UserRole::ADMIN->value]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['role']);

            expect(User::where('role', UserRole::ADMIN->value)->count())->toBe(0);
        });
    });
})->group('auth');
