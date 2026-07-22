<?php

use App\Models\User;
use App\Notifications\User\UserUnbannedNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('Admin -> UnbanUserController', function () {
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

            patchJson(route('admin.users.unban', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to unban the user', function () {
            $targetUser = User::factory()->banned()->create();

            patchJson(route('admin.users.unban', $targetUser))
                ->assertUnauthorized();

            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeTrue();
        });

        it('fails if a user without permissions tries to unban a user', function ($user) {
            Sanctum::actingAs($user);

            $targetUser = User::factory()->banned()->create();

            patchJson(route('admin.users.unban', $targetUser))
                ->assertForbidden();

            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeTrue();
        })->with([
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('fails if an authenticated user tries to unban themselves', function () {
            $admin = User::factory()->admin()->banned()->create();
            Sanctum::actingAs($admin);

            patchJson(route('admin.users.unban', $admin))
                ->assertForbidden();

            $admin->refresh();
            expect($admin->isBanned())->toBeTrue();
        });

        it('fails if an admin tries to unban another admin or super-admin', function ($targetUser) {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            patchJson(route('admin.users.unban', $targetUser))
                ->assertForbidden();
        })->with([
            'admin' => fn() => User::factory()->admin()->banned()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ]);

        it('allows an admin to unban non-admin users', function ($targetUser) {
            Notification::fake();

            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            patchJson(route('admin.users.unban', $targetUser))
                ->assertNoContent();

            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeFalse();

            Notification::assertSentTo($targetUser, UserUnbannedNotification::class);
        })->with([
            'user' => fn() => User::factory()->banned()->create(),
            'teacher' => fn() => User::factory()->teacher()->banned()->create(),
        ]);

        it('allows a super-admin to unban any user', function ($targetUser) {
            Notification::fake();

            $superAdmin = User::where('email', config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            patchJson(route('admin.users.unban', $targetUser))
                ->assertNoContent();

            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeFalse();

            Notification::assertSentTo($targetUser, UserUnbannedNotification::class);
        })->with([
            'user' => fn() => User::factory()->banned()->create(),
            'teacher' => fn() => User::factory()->teacher()->banned()->create(),
            'admin' => fn() => User::factory()->admin()->banned()->create(),
        ]);

        it('does not send a notification if the user is already unbanned', function () {
            Notification::fake();

            $superAdmin = User::where('email', config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            $targetUser = User::factory()->create();

            patchJson(route('admin.users.unban', $targetUser))
                ->assertNoContent();

            Notification::assertNothingSent();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the user cache when a user is unbanned', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $targetUser = User::factory()->banned()->create();

            $page = 1;
            $cacheKey = "teachers:page:{$page}";
            $tags = [config('cache.tags.teacher_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.teacher'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            patchJson(route('admin.users.unban', $targetUser))
                ->assertNoContent();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('user', 'admin');
