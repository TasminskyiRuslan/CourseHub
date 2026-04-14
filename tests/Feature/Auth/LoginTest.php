<?php

use App\Enums\UserRole;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('LoginController', function () {
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
            $user = User::factory()->create();

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
        it('allows if an unverified user tries to login', function () {
            $user = User::factory()->unverified()->create(['password' => 'password']);
            $data = [
                'email' => $user->email,
                'password' => 'password',
            ];

            postJson(route('auth.login'), $data)
                ->assertOk()
                ->assertJsonFragment(['email' => $data['email']])
                ->assertJsonStructure([
                    'data' => authJsonStructure(),
                ]);
        });

        it('allows if a verified user tries to login', function () {
            $user = User::factory()->verified()->create(['password' => 'password']);
            $data = [
                'email' => $user->email,
                'password' => 'password',
            ];

            postJson(route('auth.login'), $data)
                ->assertOk()
                ->assertJsonFragment(['email' => $data['email']])
                ->assertJsonStructure([
                    'data' => authJsonStructure(),
                ]);
        });

        it('allows if a student tries to login', function () {
            $student = User::factory()->student()->create(['password' => 'password']);
            $data = [
                'email' => $student->email,
                'password' => 'password',
            ];

            postJson(route('auth.login'), $data)
                ->assertOk()
                ->assertJsonFragment(['email' => $data['email']])
                ->assertJsonStructure([
                    'data' => authJsonStructure(),
                ]);
        });

        it('allows if a teacher tries to login', function () {
            $teacher = User::factory()->teacher()->create(['password' => 'password']);
            $data = [
                'email' => $teacher->email,
                'password' => 'password',
            ];

            postJson(route('auth.login'), $data)
                ->assertOk()
                ->assertJsonFragment(['email' => $data['email']])
                ->assertJsonStructure([
                    'data' => authJsonStructure(),
                ]);
        });

        it('allows if an admin tries to login', function () {
            $admin = User::factory()->admin()->create(['password' => 'password']);
            $data = [
                'email' => $admin->email,
                'password' => 'password',
            ];

            postJson(route('auth.login'), $data)
                ->assertOk()
                ->assertJsonFragment(['email' => $data['email']])
                ->assertJsonStructure([
                    'data' => authJsonStructure(),
                ]);
        });

        it('allows if a super-admin tries to login', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            $data = [
                'email' => $superAdmin->email,
                'password' => config('super-admin.password'),
            ];

            postJson(route('auth.login'), $data)
                ->assertOk()
                ->assertJsonFragment(['email' => $data['email']])
                ->assertJsonStructure([
                    'data' => authJsonStructure(),
                ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('sets a long token expiration when remember is true', function () {
            $password = 'password';
            $user = User::factory()->verified()->create(['password' => $password]);

            $response = postJson(route('auth.login'), [
                'email' => $user->email,
                'password' => $password,
                'remember' => true,
            ])->assertOk();

            $expiresAt = Carbon::parse($response->json('data.expires_at'));
            expect($expiresAt->greaterThan(now()->addMinutes(config('sanctum.token_ttl.default'))))->toBeTrue();
        });

        it('sets a short token expiration when remember is false', function () {
            $password = 'password';
            $user = User::factory()->verified()->create(['password' => $password]);

            $response = postJson(route('auth.login'), [
                'email' => $user->email,
                'password' => $password,
                'remember' => false,
            ])->assertOk();

            $expiresAt = Carbon::parse($response->json('data.expires_at'));
            expect($expiresAt->lessThanOrEqualTo(now()->addMinutes(config('sanctum.token_ttl.default'))))->toBeTrue();
        });

        it('sets a short token expiration by default', function () {
            $password = 'password';
            $user = User::factory()->verified()->create(['password' => $password]);

            $response = postJson(route('auth.login'), [
                'email' => $user->email,
                'password' => $password,
            ])->assertOk();

            $expiresAt = Carbon::parse($response->json('data.expires_at'));
            expect($expiresAt->lessThanOrEqualTo(now()->addMinutes(config('sanctum.token_ttl.default'))))->toBeTrue();
        });
    });
})->group('auth');
