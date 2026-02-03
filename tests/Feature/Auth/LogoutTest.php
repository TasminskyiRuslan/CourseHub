<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('LogoutController', function () {
    beforeEach(function () {
        $this->user = User::factory()
            ->verified()
            ->create();

        $this->tokens = collect(range(1, 5))
            ->map(fn() => $this->user->createToken('access_token'));

        $this->currentToken = $this->tokens->first();
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('revokes the current access token only', function () {
            postJson(
                route('auth.logout'),
                [],
                ['Authorization' => 'Bearer ' . $this->currentToken->plainTextToken]
            )->assertNoContent();

            expect(
                $this->user->tokens()
                    ->where('id', $this->currentToken->accessToken->id)
                    ->exists()
            )->toBeFalse()
                ->and($this->user->tokens()->count())->toBe(4);

        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails for unauthenticated user', function () {
            postJson(route('auth.logout'))
                ->assertUnauthorized();
        });
    });
})->group('auth');
