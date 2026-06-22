<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('AccountController -> show', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails for unauthenticated user', function () {
            getJson(route('auth.account.show'))
                ->assertUnauthorized();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('returns authenticated user data', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            getJson(route('auth.account.show'))
                ->assertOk()
                ->assertJsonFragment(['email' => $user->email])
                ->assertJsonStructure(['data' => userJsonStructure()]);
        });
    });
})->group('auth');
