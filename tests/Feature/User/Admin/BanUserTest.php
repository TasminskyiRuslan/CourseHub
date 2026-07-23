<?php

use App\Models\User;
use App\Notifications\User\UserBannedNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('Admin -> BanUserController', function () {
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

            patchJson(route('admin.users.ban', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to ban the user', function () {
            $targetUser = User::factory()->create();

            patchJson(route('admin.users.ban', $targetUser))
                ->assertUnauthorized();

            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeFalse();
        });

        it('fails if a user without permissions tries to ban a user', function ($user) {
            Sanctum::actingAs($user);

            $targetUser = User::factory()->create();

            patchJson(route('admin.users.ban', $targetUser))
                ->assertForbidden();

            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeFalse();
        })->with([
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('fails if an authenticated user tries to ban themselves', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            patchJson(route('admin.users.ban', $admin))
                ->assertForbidden();

            $admin->refresh();
            expect($admin->isBanned())->toBeFalse();
        });

        it('fails if an admin tries to ban another admin or super-admin', function ($targetUser) {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            patchJson(route('admin.users.ban', $targetUser))
                ->assertForbidden();

            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeFalse();
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ]);

        it('fails if a super-admin tries to ban their own user', function () {
            $superAdmin = User::where('email', config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            patchJson(route('admin.users.ban', $superAdmin))
                ->assertForbidden();

            $superAdmin->refresh();
            expect($superAdmin->isBanned())->toBeFalse();
        });

        it('allows admin to ban non-admin users', function ($targetUser) {
            Notification::fake();

            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $targetUser->createToken('access_token');

            patchJson(route('admin.users.ban', $targetUser))
                ->assertNoContent();

            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeTrue()
                ->and($targetUser->tokens()->count())->toBe(0);

            Notification::assertSentTo($targetUser, UserBannedNotification::class);
        })->with([
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows super-admin to ban any user', function ($targetUser) {
            Notification::fake();

            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            $targetUser->createToken('access_token');

            patchJson(route('admin.users.ban', $targetUser))
                ->assertNoContent();

            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeTrue()
                ->and($targetUser->tokens()->count())->toBe(0);

            Notification::assertSentTo($targetUser, UserBannedNotification::class);
        })->with([
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('does not send a notification if the user is already banned', function () {
            Notification::fake();

            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            $targetUser = User::factory()->banned()->create();

            patchJson(route('admin.users.ban', $targetUser))
                ->assertNoContent();

            Notification::assertNotSentTo($targetUser, UserBannedNotification::class);
        });

        it('fails if a banned user tries to ban a user', function () {
            $bannedUser = User::factory()->admin()->banned()->create();

            $targetUser = User::factory()->create();

            Sanctum::actingAs($bannedUser);

            patchJson(route('admin.users.ban', $targetUser))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the user cache when a user is banned', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $targetUser = User::factory()->create();

            $page = 1;
            $cacheKey = "teachers:page:{$page}";
            $tags = [config('cache.tags.teacher_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.teacher'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            patchJson(route('admin.users.ban', $targetUser))
                ->assertNoContent();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('user', 'admin');
