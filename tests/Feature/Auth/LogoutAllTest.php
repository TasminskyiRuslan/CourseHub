<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('Auth -> LogoutAllController', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to revoke tokens', function () {
            deleteJson(route('auth.tokens.destroy'))
                ->assertUnauthorized();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('revokes all authentication tokens for the user', function () {
            $user = User::factory()->create();

            collect(range(1, 5))->each(fn() => $user->createToken('access_token'));

            Sanctum::actingAs($user);

            deleteJson(route('auth.tokens.destroy'))
                ->assertNoContent();

            expect($user->fresh()->tokens()->count())->toBe(0);
        });
    });
})->group('auth');
