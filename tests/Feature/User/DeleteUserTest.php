<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('UserController -> destroy', function () {
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

            deleteJson(route('user.destroy', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to delete the user', function () {
            $targetUser = User::factory()->create();

            deleteJson(route('user.destroy', $targetUser))
                ->assertUnauthorized();

            $this->assertDatabaseHas('users', ['id' => $targetUser->id, 'deleted_at' => null]);
        });

        it('fails if users without permissions try to delete another user', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $targetUser = User::factory()->create();

            deleteJson(route('user.destroy', $targetUser))
                ->assertForbidden();
        })->with([
            'unverified' => fn() => User::factory()->unverified()->create(),
            'student' => fn() => User::factory()->student()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('fails if admin tries to delete themselves', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            deleteJson(route('user.destroy', $admin))
                ->assertForbidden();
        });

        it('fails if admin tries to delete another admin or super-admin', function ($targetUser) {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            deleteJson(route('user.destroy', $targetUser))
                ->assertForbidden();
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);

        it('allows admin to soft delete the user', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $targetUser = User::factory()->student()->create();

            deleteJson(route('user.destroy', $targetUser))
                ->assertNoContent();

            $this->assertSoftDeleted('users', ['id' => $targetUser->id]);
        });

        it('fails to delete a user who has active courses', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();

            deleteJson(route('user.destroy', $author))
                ->assertForbidden();

            $this->assertDatabaseHas('users', [
                'id' => $author->id,
                'deleted_at' => null
            ]);
        });
    });
})->group('user');
