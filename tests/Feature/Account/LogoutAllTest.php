<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('LogoutAllController', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails for an unauthenticated user', function () {
            deleteJson(route('account.tokens.destroy'))
                ->assertUnauthorized();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('revokes all authentication tokens', function () {
            $user = User::factory()->create();
            collect(range(1, 5))
                ->each(fn() => $user->createToken('access_token'));
            Sanctum::actingAs($user);

            deleteJson(route('account.tokens.destroy'))
                ->assertNoContent();
            expect($user->tokens()->count())->toBe(0);
        });
    });
})->group('account');
