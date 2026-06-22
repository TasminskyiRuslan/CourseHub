<?php

use App\Models\User;
use App\Notifications\UserBannedNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('BanUserController', function () {
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
            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            patchJson(route('user.ban', 'non-existing-slug'))
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

            patchJson(route('user.ban', $targetUser))
                ->assertUnauthorized();
            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeFalse();
        });

        it('fails if users without permissions tries to ban someone else\'s user', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $targetUser = User::factory()->create();

            patchJson(route('user.ban', $targetUser))
                ->assertForbidden();
            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeFalse();
        })->with([
            'student' => fn() => User::factory()->student()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('fails if authenticated user tries to ban their own user', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            patchJson(route('user.ban', $admin))
                ->assertForbidden();
            $admin->refresh();
            expect($admin->isBanned())->toBeFalse();
        });

        it('fails if user tries to ban admin or super admin user', function ($targetUser) {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            patchJson(route('user.ban', $targetUser))
                ->assertForbidden();
            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeFalse();
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);

        it('allows users with permissions to ban any user', function ($targetUser) {
            Notification::fake();

            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            $targetUser->createToken('access_token');

            patchJson(route('user.ban', $targetUser))
                ->assertNoContent();

            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeTrue()
                ->and($targetUser->tokens()->count())->toBe(0);

            Notification::assertSentTo($targetUser, UserBannedNotification::class);
        })->with([
            'student'      => fn() => User::factory()->student()->create(),
            'teacher'      => fn() => User::factory()->teacher()->create(),
            'admin'        => fn() => User::factory()->admin()->create(),
        ]);
    });
})->group('user');
