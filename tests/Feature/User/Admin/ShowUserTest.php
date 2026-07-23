<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('Admin -> TeacherController -> show', function () {
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

            getJson(route('admin.users.show', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to retrieve a user', function () {
            $targetUser = User::factory()->create();

            getJson(route('admin.users.show', $targetUser))
                ->assertUnauthorized();
        });

        it('fails if a user without permissions tries to retrieve a user', function ($user) {
            Sanctum::actingAs($user);

            $targetUser = User::factory()->create();

            getJson(route('admin.users.show', $targetUser))
                ->assertForbidden();
        })->with([
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows a user with permission to retrieve any user, including banned and soft-deleted', function ($userClosure, $targetUserClosure) {
            $user = $userClosure();
            Sanctum::actingAs($user);
            $targetUser = $targetUserClosure();

            getJson(route('admin.users.show', $targetUser))
                ->assertOk()
                ->assertJsonStructure(['data' => adminUserJsonStructure()]);
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'banned user' => fn() => User::factory()->banned()->create(),
            'soft-deleted user' => function () {
                $user = User::factory()->create();
                $user->delete();
                return $user;
            },
        ]);

        it('fails if a banned user tries to retrieve a user', function () {
            $bannedUser = User::factory()->admin()->banned()->create();

            $targetUser = User::factory()->create();

            Sanctum::actingAs($bannedUser);

            getJson(route('admin.users.show', $targetUser))
                ->assertForbidden();
        });
    });
})->group('user', 'admin');
