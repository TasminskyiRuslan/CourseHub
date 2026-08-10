<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('Auth -> LogoutAllController', function () {
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

            collect(range(1, 5))->each(fn () => $user->createToken('access_token'));

            Sanctum::actingAs($user);

            deleteJson(route('auth.tokens.destroy'))
                ->assertNoContent();

            expect($user->fresh()->tokens()->count())->toBe(0);
        });

        it('does not revoke tokens belonging to other users', function () {
            $user = User::factory()->create();
            $otherUser = User::factory()->create();

            $user->createToken('access_token');
            $otherUser->createToken('access_token');

            Sanctum::actingAs($user);

            deleteJson(route('auth.tokens.destroy'))
                ->assertNoContent();

            expect($user->fresh()->tokens()->count())->toBe(0)
                ->and($otherUser->fresh()->tokens()->count())->toBe(1);
        });
    });
})->group('auth');
