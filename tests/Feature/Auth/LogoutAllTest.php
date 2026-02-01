<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('LogoutAllController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        foreach (range(1, 5) as $i) {
            $this->user->createToken('access_token');
        }
    });

    it('revokes all authentication tokens for the authenticated user', function () {
        Sanctum::actingAs($this->user);

        deleteJson(route('auth.tokens.destroy',))
            ->assertNoContent();

        expect($this->user->tokens()->count())->toBe(0);
    });

    it('returns unauthorized if user is not authenticated', function () {
        deleteJson(route('auth.tokens.destroy',))
            ->assertUnauthorized();
    });
});
