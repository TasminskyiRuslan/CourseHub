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

describe('Auth -> RegisterController', function () {
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
                ->assertJsonValidationErrors(['name', 'email', 'password']);
        });

        it('fails if the email is already taken', function () {
            $data = registrationPayload();

            User::factory()->create(['email' => $data['email']]);

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

        it('fails if the roles field is not an array', function () {
            postJson(route('auth.register'), registrationPayload(['roles' => 'string-instead-of-array']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['roles']);
        });

        it('fails if the user role is invalid', function () {
            postJson(route('auth.register'), registrationPayload(['roles' => [UserRole::TEACHER->value, 'invalid-role']]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['roles.1']);
        });

        it('fails if roles contain duplicates', function () {
            postJson(route('auth.register'), registrationPayload(['roles' => [UserRole::TEACHER->value, UserRole::TEACHER->value]]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['roles.1']);
        });

        it('fails if trying to register with forbidden role', function ($invalidRole) {
            $data = registrationPayload(['roles' => [$invalidRole->value]]);

            postJson(route('auth.register'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['roles.0']);

            expect(User::where('email', $data['email'])->count())->toBe(0);
        })->with([
            'admin role' => UserRole::ADMIN,
            'super-admin role' => UserRole::SUPER_ADMIN,
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('registers a user successfully and returns an access token', function ($rolePayload, $expectedRoleInJson) {
            Event::fake();

            $data = registrationPayload($rolePayload);

            $response = postJson(route('auth.register'), $data)
                ->assertCreated()
                ->assertJsonFragment(['email' => strtolower($data['email'])])
                ->assertJsonStructure([
                    'data' => authJsonStructure(),
                ]);

            if ($expectedRoleInJson) {
                $response->assertJsonFragment(['roles' => [$expectedRoleInJson]]);
            }

            $user = User::where('email', strtolower($data['email']))->first();

            expect($user)->not->toBeNull()
                ->and(Hash::check($data['password'], $user->password))->toBeTrue();

            Event::assertDispatched(Registered::class, fn($event) => $event->user->email === $user->email);
        })
            ->with([
                'with allowed teacher role' => [
                    ['roles' => [UserRole::TEACHER->value]],
                    UserRole::TEACHER->value
                ],
                'without role (default registration)' => [
                    [],
                    null
                ],
            ]);

        it('sets a short token expiration by default', function () {
            $response = postJson(route('auth.register'), registrationPayload())
                ->assertCreated();

            $expiresAt = Carbon::parse($response->json('data.expires_at'));

            expect($expiresAt->lessThanOrEqualTo(now()->addMinutes(config('sanctum.token_ttl.default'))))->toBeTrue();
        });
    });
})->group('auth');
