<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('Admin -> UserController -> destroy', function () {
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

        it('fails if a user tries to delete themselves', function (User $user) {
            Sanctum::actingAs($user);

            deleteJson(route('admin.users.destroy', $user))
                ->assertForbidden();

            $this->assertNotSoftDeleted($user);
        })->with([
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ]);

        it('fails if an admin tries to delete another admin or super-admin', function (User $targetUser) {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            deleteJson(route('admin.users.destroy', $targetUser))
                ->assertForbidden();

            $this->assertNotSoftDeleted($targetUser);
        })->with([
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
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

            $this->assertNotSoftDeleted($targetUser);
        });

        it('fails if a user without permissions tries to delete a user', function (?User $user) {
            Sanctum::actingAs($user);

            $targetUser = User::factory()->create();

            deleteJson(route('admin.users.destroy', $targetUser))
                ->assertForbidden();

            $this->assertNotSoftDeleted($targetUser);
        })->with([
            'user' => fn () => User::factory()->create(),
            'unverified teacher' => fn () => User::factory()->teacher()->unverified()->create(),
            'teacher' => fn () => User::factory()->teacher()->create(),
        ]);

        it('allows a user with permission to delete a user', function (?User $user, ?User $targetUser) {
            Sanctum::actingAs($user);

            deleteJson(route('admin.users.destroy', $targetUser))
                ->assertNoContent();

            $this->assertSoftDeleted($targetUser);
        })->with([
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'active user' => fn () => User::factory()->create(),
            'banned user' => fn () => User::factory()->banned()->create(),
        ]);

        it('fails if a banned user tries to delete a user', function () {
            $bannedUser = User::factory()->admin()->banned()->create();

            $targetUser = User::factory()->create();

            Sanctum::actingAs($bannedUser);

            deleteJson(route('admin.users.destroy', $targetUser))
                ->assertForbidden();
        });
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
