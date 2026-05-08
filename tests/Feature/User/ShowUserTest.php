<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('UserController -> show', function () {
    beforeEach(function () {
        Cache::flush();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the user does not exist', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            getJson(route('user.show', 999))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if unauthenticated user tries to retrieve the user', function () {
            $targetUser = User::factory()->create();

            getJson(route('user.show', $targetUser))
                ->assertUnauthorized();
        });

        it('fails if users without permissions tries to retrieve the user', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $targetUser = User::factory()->create();

            getJson(route('user.show', $targetUser))
                ->assertForbidden();
        })->with([
            'unverified' => fn() => User::factory()->unverified()->create(),
            'student' => fn() => User::factory()->student()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows users with permissions to retrieve the user', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $targetUser = User::factory()->student()->create();

            getJson(route('user.show', $targetUser))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => userJsonStructure()
                ]);
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);
    });
})->group('user');
