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

            getJson(route('admin.user.show', 'non-existing-slug'))
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

            getJson(route('admin.user.show', $targetUser))
                ->assertUnauthorized();
        });

        it('fails if users without permissions tries to retrieve the user', function ($user) {
            Sanctum::actingAs($user);

            $targetUser = User::factory()->create();

            getJson(route('admin.user.show', $targetUser))
                ->assertForbidden();
        })->with([
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows users with permissions to retrieve any user including banned and trashed', function ($userClosure, $targetUserClosure) {
            $user = $userClosure();
            Sanctum::actingAs($user);
            $targetUser = $targetUserClosure();

            getJson(route('admin.user.show', $targetUser))
                ->assertOk()
                ->assertJsonStructure(['data' => adminUserJsonStructure()]);
        })->with([
            'admin'       => fn() => User::factory()->admin()->create(),
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
    });
})->group('user', 'admin');
