<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('Admin -> TeacherController -> destroy', function () {
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

            deleteJson(route('admin.users.destroy', 'non-existing-slug'))
                ->assertNotFound();
        });

        it('fails if a user tries to delete themselves', function ($user) {
            Sanctum::actingAs($user);

            deleteJson(route('admin.users.destroy', $user))
                ->assertForbidden();

            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'deleted_at' => null,
            ]);
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ]);

        it('fails if an admin tries to delete another admin or super-admin', function ($targetUser) {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            deleteJson(route('admin.users.destroy', $targetUser))
                ->assertForbidden();

            $this->assertDatabaseHas('users', [
                'id' => $targetUser->id,
                'deleted_at' => null,
            ]);
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to delete the user', function () {
            $targetUser = User::factory()->create();

            deleteJson(route('admin.users.destroy', $targetUser))
                ->assertUnauthorized();

            $this->assertDatabaseHas('users', [
                'id' => $targetUser->id,
            ]);
        });

        it('fails if a user without permissions tries to delete a user', function ($user) {
            Sanctum::actingAs($user);

            $targetUser = User::factory()->create();

            deleteJson(route('admin.users.destroy', $targetUser))
                ->assertForbidden();

            $this->assertDatabaseHas('users', [
                'id' => $targetUser->id,
            ]);
        })->with([
            'user' => fn() => User::factory()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows a user with permission to delete a user', function ($userClosure, $targetUserClosure) {
            Sanctum::actingAs($userClosure());

            $targetUser = $targetUserClosure();

            deleteJson(route('admin.users.destroy', $targetUser))
                ->assertNoContent();

            $this->assertSoftDeleted('users', [
                'id' => $targetUser->id,
            ]);
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'active user' => fn() => User::factory()->create(),
            'banned user' => fn() => User::factory()->banned()->create(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the user cache when a user is deleted', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $targetUser = User::factory()->create();

            $page = 1;
            $cacheKey = "teachers:page:{$page}";
            $tags = [config('cache.tags.teacher_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.teacher'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            deleteJson(route('admin.users.destroy', $targetUser))
                ->assertNoContent();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('user', 'admin');
