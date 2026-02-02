<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('MeController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();

        $this->expectedUserStructure = [
            'id',
            'name',
            'slug',
            'email',
            'role',
            'email_verified_at',
            'created_at',
            'updated_at',
        ];
    });

    describe('when authenticated', function () {
        beforeEach(function () {
            Sanctum::actingAs($this->user);
        });

        it('returns authenticated user data', function () {
            getJson(route('auth.me'))
                ->assertOk()
                ->assertJsonStructure(['data' => $this->expectedUserStructure]);
        });
    });

    describe('permissions', function () {
        it('fails for unauthenticated user', function () {
            getJson(route('auth.me'))
                ->assertUnauthorized();
        });
    });
})->group('auth');
