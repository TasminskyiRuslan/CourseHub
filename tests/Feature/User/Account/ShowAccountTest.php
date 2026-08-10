<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('Account -> AccountController -> show', function () {
    beforeEach(function () {
        Cache::flush();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to retrieve the account data', function () {
            getJson(route('account.show'))
                ->assertUnauthorized();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('returns the authenticated user data', function (?User $user) {
            Sanctum::actingAs($user);

            getJson(route('account.show'))
                ->assertOk()
                ->assertJsonFragment(['email' => $user->email])
                ->assertJsonStructure(['data' => accountUserJsonStructure()]);
        })->with([
            'user' => fn () => User::factory()->create(),
            'teacher' => fn () => User::factory()->teacher()->create(),
            'unverified teacher' => fn () => User::factory()->teacher()->unverified()->create(),
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ]);
    });
})->group('user', 'account');
