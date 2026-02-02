<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('LogoutAllController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();

        collect(range(1, 5))
            ->map(fn () => $this->user->createToken('access_token'));
    });

    describe('success', function () {
        beforeEach(function () {
            Sanctum::actingAs($this->user);
        });

        it('revokes all authentication tokens for the user', function () {
            deleteJson(route('auth.tokens.destroy'))
                ->assertNoContent();

            expect($this->user->tokens()->count())->toBe(0);
        });
    });

    describe('permissions', function () {
        it('fails for unauthenticated user', function () {
            deleteJson(route('auth.tokens.destroy'))
                ->assertUnauthorized();
        });
    });
})->group('auth');
