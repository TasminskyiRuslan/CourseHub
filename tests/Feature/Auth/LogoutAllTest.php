<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('LogoutAllController', function () {
    beforeEach(function () {
        $this->user = User::factory()
            ->verified()
            ->create();

        collect(range(1, 5))
            ->map(fn() => $this->user->createToken('access_token'));
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('revokes all authentication tokens for the user', function () {
            Sanctum::actingAs($this->user);

            deleteJson(route('auth.tokens.destroy'))
                ->assertNoContent();

            expect($this->user->tokens()->count())->toBe(0);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails for unauthenticated user', function () {
            deleteJson(route('auth.tokens.destroy'))
                ->assertUnauthorized();
        });
    });
})->group('auth');
