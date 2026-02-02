<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('LogoutController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();

        $this->tokens = [];
        foreach (range(1, 5) as $i) {
            $this->tokens[] = $this->user->createToken('access_token');
        }

        $this->currentToken = $this->tokens[0];
    });

    it('revokes only the current token for the authenticated user', function () {
        postJson(route('auth.logout'), [], [
            'Authorization' => 'Bearer ' . $this->currentToken->plainTextToken,
        ])->assertNoContent();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $this->currentToken->accessToken->id
        ]);

        expect($this->user->tokens()->where('id', $this->currentToken->accessToken->id)->exists())->toBeFalse()
            ->and($this->user->tokens()->count())->toBe(4);
    });

    it('returns unauthorized if user is not authenticated', function () {
        postJson(route('auth.logout'))
            ->assertUnauthorized();
    });
});
