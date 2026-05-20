<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

describe('UserRoleController -> update', function () {
    beforeEach(function () {
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

            putJson(route('user.role.update', 'non-existing-slug'), ['role' => UserRole::TEACHER->value])
                ->assertNotFound();
        });

        it('fails if the required fields are missing', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->student()->create();

            putJson(route('user.role.update', $targetUser), [])
                ->assertUnprocessable();
        });

        it('fails if the user role is invalid', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->student()->create();

            putJson(route('user.role.update', $targetUser), ['role' => 'invalid-role'])
                ->assertUnprocessable();
        });

        it('fails if the user role is being updated to a super-admin', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->student()->create();

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::SUPER_ADMIN->value])
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
            $targetUser = User::factory()->student()->create();

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::TEACHER->value])
                ->assertUnauthorized();
        });

        it('fails if users without permission try to update a user\'s role', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }
            $targetUser = User::factory()->student()->create();

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::TEACHER->value])
                ->assertForbidden();
        })->with([
            'unverified' => fn() => User::factory()->unverified()->create(),
            'student' => fn() => User::factory()->student()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('fails if an admin tries to update another admin\'s role', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetAdmin = User::factory()->admin()->create();

            putJson(route('user.role.update', $targetAdmin), ['role' => UserRole::TEACHER->value])
                ->assertForbidden();
        });

        it('fails if an admin tries to update the super-admin\'s role', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $superAdmin = User::factory()->create();
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);

            putJson(route('user.role.update', $superAdmin), ['role' => UserRole::TEACHER->value])
                ->assertForbidden();
        });

        it('fails if an admin tries to update his own role', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            putJson(route('user.role.update', $admin), ['role' => UserRole::TEACHER->value])
                ->assertForbidden();
        });

        it('allows an admin to update a user\'s role', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->student()->create();

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::TEACHER->value])
                ->assertOk()
                ->assertJsonStructure(['data' => userJsonStructure()]);

            $targetUser->refresh();
            expect($targetUser->hasRole(UserRole::STUDENT))->toBeFalse()
                ->and($targetUser->hasRole(UserRole::TEACHER))->toBeTrue();
        });

        it('allows a super-admin to update any user\'s role', function () {
            $superAdmin = User::factory()->create();
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);

            $targetUser = User::factory()->admin()->create();

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::STUDENT->value])
                ->assertOk()
                ->assertJsonStructure(['data' => userJsonStructure()]);

            $targetUser->refresh();
            expect($targetUser->hasRole(UserRole::ADMIN))->toBeFalse()
                ->and($targetUser->hasRole(UserRole::STUDENT))->toBeTrue();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | business logic
    |--------------------------------------------------------------------------
    */
    describe('business logic', function () {
        it('does nothing when assigning the same role', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->student()->create();

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::STUDENT->value])
                ->assertOk()
                ->assertJsonStructure(['data' => userJsonStructure()]);

            $targetUser->refresh();
            expect($targetUser->hasRole(UserRole::STUDENT))->toBeTrue();
        });
    });
})->group('user');
