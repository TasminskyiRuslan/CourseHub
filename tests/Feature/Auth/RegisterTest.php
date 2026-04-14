<?php

use App\Enums\UserRole;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('RegisterController', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the required fields are missing', function () {
            postJson(route('auth.register'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
        });

        it('fails if the email is already taken', function () {
            $data = registrationPayload();

            User::factory()->verified()->create(['email' => $data['email']]);

            postJson(route('auth.register'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the email format is invalid', function () {
            postJson(route('auth.register'), registrationPayload(['email' => 'invalid-email']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the password is too short', function () {
            postJson(route('auth.register'), registrationPayload([
                'password' => '123',
                'password_confirmation' => '123',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });

        it('fails if the password confirmation does not match', function () {
            postJson(route('auth.register'), registrationPayload([
                'password_confirmation' => 'different',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });

        it('fails if the role is invalid', function () {
            postJson(route('auth.register'), registrationPayload(['role' => 'invalid-role']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['role']);
        });

        it('fails if the role is an admin', function () {
            $data = registrationPayload(['role' => UserRole::ADMIN->value]);
            postJson(route('auth.register'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['role']);

            expect(User::where('email', $data['email'])->count())->toBe(0);
        });

        it('fails if the role is a super-admin', function () {
            $data = registrationPayload(['role' => UserRole::SUPER_ADMIN->value]);
            postJson(route('auth.register'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['role']);

            expect(User::where('email', $data['email'])->count())->toBe(0);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('registers a user and return an access token if the role is a student', function () {
            Event::fake();
            $data = registrationPayload(['role' => UserRole::STUDENT->value]);

            postJson(route('auth.register'), $data)
                ->assertCreated()
                ->assertJsonFragment(['email' => $data['email']])
                ->assertJsonFragment(['role' => UserRole::STUDENT->value])
                ->assertJsonStructure([
                    'data' => authJsonStructure(),
                ]);
            $user = User::whereEmail($data['email'])->first();
            expect($user)->not->toBeNull()
                ->and(Hash::check($data['password'], $user->password))->toBeTrue();
            Event::assertDispatched(Registered::class, fn($event) => $event->user->email === $data['email']);
        });

        it('registers a user and return an access token if the role is a teacher', function () {
            Event::fake();
            $data = registrationPayload(['role' => UserRole::TEACHER->value]);

            postJson(route('auth.register'), $data)
                ->assertCreated()
                ->assertJsonFragment(['email' => $data['email']])
                ->assertJsonFragment(['role' => UserRole::TEACHER->value])
                ->assertJsonStructure([
                    'data' => authJsonStructure(),
                ]);
            $user = User::whereEmail($data['email'])->first();
            expect($user)->not->toBeNull()
                ->and(Hash::check($data['password'], $user->password))->toBeTrue();
            Event::assertDispatched(Registered::class, fn($event) => $event->user->email === $data['email']);
        });

        it('sets a short token expiration by default', function () {
            $response = postJson(route('auth.register'), registrationPayload())
                ->assertCreated();

            $expiresAt = Carbon::parse($response->json('data.expires_at'));
            expect($expiresAt->lessThanOrEqualTo(now()->addMinutes(config('sanctum.token_ttl.default'))))->toBeTrue();
        });
    });
})->group('auth');
