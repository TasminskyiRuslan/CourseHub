<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\UserJsonStructure;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('MeController', function () {
    beforeEach(function () {
        $this->user = User::factory()
            ->verified()
            ->create();
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('returns authenticated user data', function () {
            Sanctum::actingAs($this->user);

            getJson(route('auth.me'))
                ->assertOk()
                ->assertJsonStructure(['data' => UserJsonStructure::get()]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails for unauthenticated user', function () {
            getJson(route('auth.me'))
                ->assertUnauthorized();
        });
    });
})->group('auth');
