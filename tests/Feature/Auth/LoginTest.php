<?php

use Illuminate\Support\Facades\Event;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('Auth -> LoginController', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the required fields are missing', function () {
            postJson(route('auth.login'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email', 'password']);
        });

        it('fails if the email does not exist', function () {
            postJson(route('auth.login'), [
                'email' => 'nonexistent@example.com',
                'password' => 'password',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the email format is invalid', function () {
            postJson(route('auth.login'), [
                'email' => 'invalid-email',
                'password' => 'password',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the credentials are incorrect', function () {
            $user = User::factory()->create(['password' => 'correct-password']);

            postJson(route('auth.login'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('allows users to login', function ($user) {
            Event::fake();

            $data = [
                'email' => $user->email,
                'password' => 'secret',
            ];

            postJson(route('auth.login'), $data)
                ->assertOk()
                ->assertJsonFragment(['email' => $data['email']])
                ->assertJsonStructure([
                    'data' => authJsonStructure(),
                ]);

            Event::assertDispatched(Login::class, fn ($event) => $event->user->id === $user->id);
        })
        ->with([
            'unverified user' => fn() => User::factory()->unverified()->create(['password' => 'secret']),
            'verified user' => fn() => User::factory()->create(['password' => 'secret']),
            'student' => fn() => User::factory()->create(['password' => 'secret']),
            'teacher' => fn() => User::factory()->teacher()->create(['password' => 'secret']),
            'admin' => fn() => User::factory()->admin()->create(['password' => 'secret']),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('sets a long token expiration and passes remember state to event when remember is true', function () {
            Event::fake();

            $password = 'password';
            $user = User::factory()->create(['password' => $password]);

            $response = postJson(route('auth.login'), [
                'email' => $user->email,
                'password' => $password,
                'remember' => true,
            ])->assertOk();

            $expiresAt = Carbon::parse($response->json('data.expires_at'));
            expect($expiresAt->greaterThan(now()->addMinutes(config('sanctum.token_ttl.default'))))->toBeTrue();

            Event::assertDispatched(Login::class, function ($event) use ($user) {
                return $event->user->id === $user->id;
            });
        });

        it('sets a short token expiration when remember is false', function () {
            Event::fake();

            $password = 'password';
            $user = User::factory()->create(['password' => $password]);

            $response = postJson(route('auth.login'), [
                'email' => $user->email,
                'password' => $password,
                'remember' => false,
            ])->assertOk();

            $expiresAt = Carbon::parse($response->json('data.expires_at'));
            expect($expiresAt->lessThanOrEqualTo(now()->addMinutes(config('sanctum.token_ttl.default'))))->toBeTrue();

            Event::assertDispatched(Login::class, function ($event) use ($user) {
                return $event->user->id === $user->id;
            });
        });

        it('sets a short token expiration by default if remember field is missing', function () {
            Event::fake();

            $password = 'password';
            $user = User::factory()->create(['password' => $password]);

            $response = postJson(route('auth.login'), [
                'email' => $user->email,
                'password' => $password,
            ])->assertOk();

            $expiresAt = Carbon::parse($response->json('data.expires_at'));
            expect($expiresAt->lessThanOrEqualTo(now()->addMinutes(config('sanctum.token_ttl.default'))))->toBeTrue();

            Event::assertDispatched(Login::class, function ($event) use ($user) {
                return $event->user->id === $user->id && $event->remember === false;
            });
        });
    });
})->group('auth');
