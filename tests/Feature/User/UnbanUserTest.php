<?php

use App\Models\User;
use App\Notifications\UserUnbannedNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('UnbanUserController', function () {
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

            patchJson(route('user.unban', 'non-existing-slug'))
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

            patchJson(route('user.unban', $targetUser))
                ->assertUnauthorized();
            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeTrue();
        });

        it('fails if users without permissions tries to unban someone else\'s user', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $targetUser = User::factory()->banned()->create();

            patchJson(route('user.unban', $targetUser))
                ->assertForbidden();
            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeTrue();
        })->with([
            'student' => fn() => User::factory()->student()->banned()->create(),
            'teacher' => fn() => User::factory()->teacher()->banned()->create(),
        ]);

        it('fails if authenticated user tries to unban their own user', function () {
            $admin = User::factory()->admin()->banned()->create();
            Sanctum::actingAs($admin);

            patchJson(route('user.unban', $admin))
                ->assertForbidden();
            $admin->refresh();
            expect($admin->isBanned())->toBeTrue();
        });

        it('fails if user tries to unban admin user', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $targetUser = User::factory()->admin()->banned()->create();

            patchJson(route('user.unban', $targetUser))
                ->assertForbidden();
            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeTrue();
        });

        it('allows users with permissions to unban any user', function ($targetUser) {
            Notification::fake();

            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            patchJson(route('user.unban', $targetUser))
                ->assertNoContent();

            $targetUser->refresh();
            expect($targetUser->isBanned())->toBeFalse();

            Notification::assertSentTo($targetUser, UserUnbannedNotification::class);
        })->with([
            'student'      => fn() => User::factory()->student()->banned()->create(),
            'teacher'      => fn() => User::factory()->teacher()->banned()->create(),
            'admin'        => fn() => User::factory()->admin()->banned()->create(),
        ]);

        it('does not send a notification if the user is already unbanned', function () {
            Notification::fake();

            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            $targetUser = User::factory()->create();

            patchJson(route('user.unban', $targetUser))
                ->assertNoContent();

            Notification::assertNotSentTo($targetUser, UserUnbannedNotification::class);
        });
    });
})->group('user');
