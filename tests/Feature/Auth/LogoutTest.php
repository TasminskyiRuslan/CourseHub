<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('Auth -> LogoutController', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails for unauthenticated user', function () {
            deleteJson(route('auth.token.destroy'))
                ->assertUnauthorized();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('revokes the current access token only', function () {
            $user = User::factory()->create();

            collect(range(1, 4))->each(fn() => $user->createToken('access_token'));

            Sanctum::actingAs($user);

            deleteJson(route('auth.token.destroy'))
                ->assertNoContent();

            expect($user->tokens()->count())->toBe(4);
        });
    });
})->group('auth');
