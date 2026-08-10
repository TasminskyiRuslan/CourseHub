<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

describe('Admin -> UserRoleController -> update', function () {
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

            putJson(route('admin.users.role.update', 'non-existing-slug'), ['roles' => [UserRole::TEACHER->value]])
                ->assertNotFound();
        });

        it('fails if the required fields are missing', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->create();

            putJson(route('admin.users.role.update', $targetUser), [])
                ->assertUnprocessable();
        });

        it('fails if the roles field is not an array', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->create();

            putJson(route('admin.users.role.update', $targetUser), ['roles' => 'string-instead-of-array'])
                ->assertUnprocessable();
        });

        it('fails if the user role is invalid', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->create();

            putJson(route('admin.users.role.update', $targetUser), ['roles' => [UserRole::TEACHER->value, 'invalid-role']])
                ->assertUnprocessable();
        });

        it('fails if roles contain duplicates', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->create();

            putJson(route('admin.users.role.update', $targetUser), ['roles' => [UserRole::TEACHER->value, UserRole::TEACHER->value]])
                ->assertUnprocessable();
        });

        it('fails if a regular admin tries to assign restricted roles', function (string $role) {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->create();

            putJson(route('admin.users.role.update', $targetUser), ['roles' => [$role]])
                ->assertUnprocessable();
        })->with([
            'admin role' => fn () => UserRole::ADMIN->value,
            'super-admin role' => fn () => UserRole::SUPER_ADMIN->value,
        ]);

        it('fails if a super-admin tries to assign a super-admin role', function () {
            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);
            $targetUser = User::factory()->create();

            putJson(route('admin.users.role.update', $targetUser), ['roles' => [UserRole::SUPER_ADMIN->value]])
                ->assertUnprocessable();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to update the user\'s role', function () {
            $targetUser = User::factory()->create();

            putJson(route('admin.users.role.update', $targetUser), ['roles' => [UserRole::TEACHER->value]])
                ->assertUnauthorized();
        });

        it('fails if a user without permission tries to update a user\'s role', function (User $user) {
            Sanctum::actingAs($user);

            $targetUser = User::factory()->create();

            putJson(route('admin.users.role.update', $targetUser), ['roles' => [UserRole::TEACHER->value]])
                ->assertForbidden();
        })->with([
            'unverified' => fn () => User::factory()->unverified()->create(),
            'user' => fn () => User::factory()->create(),
            'teacher' => fn () => User::factory()->teacher()->create(),
        ]);

        it('fails if an admin tries to update an admin or super-admin role', function (User $targetUser) {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            putJson(route('admin.users.role.update', $targetUser), ['roles' => [UserRole::TEACHER->value]])
                ->assertForbidden();
        })->with([
            'another admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ]);

        it('fails if an admin tries to update their own role', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            putJson(route('admin.users.role.update', $admin), ['roles' => [UserRole::TEACHER->value]])
                ->assertForbidden();
        });

        it('fails if a super-admin tries to update their own role', function () {
            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            putJson(route('admin.users.role.update', $superAdmin), ['roles' => [UserRole::TEACHER->value]])
                ->assertForbidden();

            $superAdmin->refresh();
            expect($superAdmin->hasRole(UserRole::SUPER_ADMIN->value))->toBeTrue();
        });

        it('allows a regular admin to update allowed roles for a user', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->create();

            putJson(route('admin.users.role.update', $targetUser), ['roles' => [UserRole::TEACHER->value]])
                ->assertOk()
                ->assertJsonStructure(['data' => adminUserJsonStructure()]);

            $targetUser->refresh();
            expect($targetUser->hasRole(UserRole::TEACHER->value))->toBeTrue();
        });

        it('allows a super-admin to assign an admin role to a user', function () {
            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);
            $targetUser = User::factory()->create();

            putJson(route('admin.users.role.update', $targetUser), ['roles' => [UserRole::TEACHER->value, UserRole::ADMIN->value]])
                ->assertOk()
                ->assertJsonStructure(['data' => adminUserJsonStructure()]);

            $targetUser->refresh();
            expect($targetUser->hasAllRoles([UserRole::TEACHER->value, UserRole::ADMIN->value]))->toBeTrue();
        });

        it('fails if a banned user tries to update the user\'s role', function () {
            $bannedUser = User::factory()->admin()->banned()->create();
            $targetUser = User::factory()->create();

            Sanctum::actingAs($bannedUser);

            putJson(route('admin.users.role.update', $targetUser), ['roles' => [UserRole::TEACHER->value]])
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('clears all roles when an empty array is provided', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $targetUser = User::factory()->create();
            $targetUser->assignRole([UserRole::TEACHER->value]);

            putJson(route('admin.users.role.update', $targetUser), ['roles' => []])
                ->assertOk();

            expect($targetUser->fresh()->roles)->toBeEmpty();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the user cache when a user role is updated', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $targetUser = User::factory()->create();

            $page = 1;
            $cacheKey = "teachers:page:{$page}";
            $tags = [config('cache.tags.teacher_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.teacher'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            putJson(route('admin.users.role.update', $targetUser), ['roles' => [UserRole::TEACHER->value]])
                ->assertOk();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('user', 'admin');
