<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        it('fails if an unauthenticated user tries to log out', function () {
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
        describe('operations', function () {
            it('revokes the current access token only', function () {
                $user = User::factory()->create();

                collect(range(1, 3))->each(fn () => $user->createToken('other_token'));

                $token = $user->createToken('current_token');

                deleteJson(
                    route('auth.token.destroy'),
                    headers: ['Authorization' => 'Bearer '.$token->plainTextToken]
                )->assertNoContent();

                expect($user->fresh()->tokens()->count())->toBe(3)
                    ->and($user->fresh()->tokens()->where('id', $token->accessToken->id)->exists())->toBeFalse();
            });
        });
    });
})->group('auth');
