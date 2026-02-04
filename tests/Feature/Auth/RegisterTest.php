<?php
//
//use App\Enums\UserRole;
//use App\Models\User;
//use Illuminate\Auth\Events\Registered;
//use Illuminate\Foundation\Testing\RefreshDatabase;
//use Illuminate\Support\Facades\Event;
//use Tests\Support\AuthJsonStructure;
//use function Pest\Laravel\postJson;
//
//uses(RefreshDatabase::class);
//
//describe('RegisterController', function () {
//
//    beforeEach(function () {
//        $this->name = 'John Doe';
//        $this->email = 'john@example.com';
//        $this->password = 'password';
//
//        $this->makePayload = fn(array $overrides = []) => array_merge([
//            'name' => $this->name,
//            'email' => $this->email,
//            'password' => $this->password,
//            'password_confirmation' => $this->password,
//            'role' => UserRole::STUDENT->value,
//        ], $overrides);
//    });
//
//    /*
//    |--------------------------------------------------------------------------
//    | success
//    |--------------------------------------------------------------------------
//    */
//    describe('success', function () {
//
//        it('registers a user', function () {
//            $data = ($this->makePayload)();
//
//            postJson(route('auth.register'), $data)
//                ->assertCreated()
//                ->assertJsonPath('data.user.email', $data['email'])
//                ->assertJsonStructure([
//                    'data' => AuthJsonStructure::get(),
//                ]);
//
//            expect(User::where('email', $this->email)->exists())->toBeTrue();
//        });
//
//        it('dispatches the registered event', function () {
//            Event::fake();
//
//            $data = ($this->makePayload)();
//
//            postJson(route('auth.register'), $data)
//                ->assertCreated();
//
//            Event::assertDispatched(Registered::class, fn($event) => $event->user->email === $this->email);
//        });
//
//        it('sets a long token expiration when remember is true', function () {
//            $data = ($this->makePayload)(['remember' => true]);
//
//            $response = postJson(route('auth.register'), $data)
//                ->assertCreated();
//
//            $expiresAt = now()->parse($response->json('data.expires_at'));
//            expect($expiresAt->greaterThan(now()->addWeek()))->toBeTrue();
//        });
//
//        it('sets a short token expiration when remember is false', function () {
//            $data = ($this->makePayload)(['remember' => false]);
//
//            $response = postJson(route('auth.register'), $data)
//                ->assertCreated();
//
//            $expiresAt = now()->parse($response->json('data.expires_at'));
//            expect($expiresAt->lessThanOrEqualTo(now()->addDay()))->toBeTrue();
//        });
//
//        it('sets a short token expiration by default', function () {
//            $data = ($this->makePayload)();
//
//            $response = postJson(route('auth.register'), $data)
//                ->assertCreated();
//
//            $expiresAt = now()->parse($response->json('data.expires_at'));
//            expect($expiresAt->lessThanOrEqualTo(now()->addDay()))->toBeTrue();
//        });
//    });
//
//    /*
//    |--------------------------------------------------------------------------
//    | validation
//    |--------------------------------------------------------------------------
//    */
//    describe('validation', function () {
//
//        it('fails when required fields are missing', function () {
//            postJson(route('auth.register'), [])
//                ->assertUnprocessable()
//                ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
//        });
//
//        it('fails when the email is already taken', function () {
//            User::factory()->verified()->create(['email' => $this->email]);
//
//            $data = ($this->makePayload)();
//
//            postJson(route('auth.register'), $data)
//                ->assertUnprocessable()
//                ->assertJsonValidationErrors(['email']);
//        });
//
//        it('fails when the email format is invalid', function () {
//            $data = ($this->makePayload)(['email' => 'invalid-email']);
//
//            postJson(route('auth.register'), $data)
//                ->assertUnprocessable()
//                ->assertJsonValidationErrors(['email']);
//        });
//
//        it('fails when the password is too short', function () {
//            $data = ($this->makePayload)([
//                'password' => '123',
//                'password_confirmation' => '123',
//            ]);
//
//            postJson(route('auth.register'), $data)
//                ->assertUnprocessable()
//                ->assertJsonValidationErrors(['password']);
//        });
//
//        it('fails when the password confirmation does not match', function () {
//            $data = ($this->makePayload)([
//                'password_confirmation' => 'different',
//            ]);
//
//            postJson(route('auth.register'), $data)
//                ->assertUnprocessable()
//                ->assertJsonValidationErrors(['password']);
//        });
//
//        it('fails when the role is invalid', function () {
//            $data = ($this->makePayload)(['role' => 'invalid-role']);
//
//            postJson(route('auth.register'), $data)
//                ->assertUnprocessable()
//                ->assertJsonValidationErrors(['role']);
//        });
//
//        it('fails when attempting to register as an admin', function () {
//            $data = ($this->makePayload)(['role' => UserRole::ADMIN->value]);
//
//            postJson(route('auth.register'), $data)
//                ->assertUnprocessable()
//                ->assertJsonValidationErrors(['role']);
//
//            expect(User::where('role', UserRole::ADMIN)->count())->toBe(0);
//        });
//    });
//})->group('auth');

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;
use Tests\Support\AuthJsonStructure;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('RegisterController', function () {

    beforeEach(function () {
        $this->name = 'John Doe';
        $this->email = 'john@example.com';
        $this->password = 'password';

        $this->makePayload = fn(array $overrides = []) => array_merge([
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
            $data = ($this->makePayload)();

            postJson(route('auth.register'), $data)
                ->assertCreated()
                ->assertJsonPath('data.user.email', $data['email'])
                ->assertJsonStructure([
                    'data' => AuthJsonStructure::get(),
                ]);

            expect(User::where('email', $this->email)->exists())->toBeTrue();
        });

        it('dispatches the registered event', function () {
            Event::fake();

            $data = ($this->makePayload)();

            postJson(route('auth.register'), $data)
                ->assertCreated();

            Event::assertDispatched(Registered::class, fn($event) => $event->user->email === $this->email);
        });

        it('sets a long token expiration when remember is true', function () {
            Carbon::setTestNow(now());
            $data = ($this->makePayload)(['remember' => true]);

            $response = postJson(route('auth.register'), $data)
                ->assertCreated();

            $expiresAt = Carbon::parse($response->json('data.expires_at'));
            expect($expiresAt->greaterThan(Carbon::now()->copy()->addWeek()))->toBeTrue();

            Carbon::setTestNow();
        });

        it('sets a short token expiration when remember is false', function () {
            Carbon::setTestNow(now());
            $data = ($this->makePayload)(['remember' => false]);

            $response = postJson(route('auth.register'), $data)
                ->assertCreated();

            $expiresAt = Carbon::parse($response->json('data.expires_at'));
            expect($expiresAt->lessThanOrEqualTo(Carbon::now()->copy()->addDay()))->toBeTrue();

            Carbon::setTestNow();
        });

        it('sets a short token expiration by default', function () {
            Carbon::setTestNow(now());
            $data = ($this->makePayload)();

            $response = postJson(route('auth.register'), $data)
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
            User::factory()->verified()->create(['email' => $this->email]);

            $data = ($this->makePayload)();

            postJson(route('auth.register'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails when the email format is invalid', function () {
            $data = ($this->makePayload)(['email' => 'invalid-email']);

            postJson(route('auth.register'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails when the password is too short', function () {
            $data = ($this->makePayload)([
                'password' => '123',
                'password_confirmation' => '123',
            ]);

            postJson(route('auth.register'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });

        it('fails when the password confirmation does not match', function () {
            $data = ($this->makePayload)([
                'password_confirmation' => 'different',
            ]);

            postJson(route('auth.register'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });

        it('fails when the role is invalid', function () {
            $data = ($this->makePayload)(['role' => 'invalid-role']);

            postJson(route('auth.register'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['role']);
        });

        it('fails when attempting to register as an admin', function () {
            $data = ($this->makePayload)(['role' => UserRole::ADMIN->value]);

            postJson(route('auth.register'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['role']);

            expect(User::where('role', UserRole::ADMIN->value)->count())->toBe(0);
        });
    });
})->group('auth');
