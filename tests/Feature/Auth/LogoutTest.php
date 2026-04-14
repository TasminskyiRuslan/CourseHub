<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('LogoutController', function () {
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
            deleteJson(route('auth.logout'))
                ->assertUnauthorized();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('revokes the current access token only', function () {
            $user = User::factory()->verified()->create();

            Sanctum::actingAs($user);

            collect(range(1, 4))
                ->map(fn() => $user->createToken('access_token'));

            deleteJson(route('auth.logout'))
                ->assertNoContent();

            expect(
                $user
                    ->tokens()
                    ->count()
            )->toBe(4);
        });
    });
})->group('auth');
