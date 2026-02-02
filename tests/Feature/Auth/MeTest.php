<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('MeController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    describe('when authenticated', function () {
        beforeEach(function () {
            Sanctum::actingAs($this->user);
        });

        it('returns the authenticated user details', function () {
            getJson(route('auth.me'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'slug',
                        'email',
                        'role',
                        'email_verified_at',
                        'created_at',
                        'updated_at',
                    ]
                ]);
        });
    });

    describe('when not authenticated', function () {
        it('fails when the user is not authenticated', function () {
            getJson(route('auth.me'))
                ->assertUnauthorized();
        });
    });
});
