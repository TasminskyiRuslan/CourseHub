<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('Account -> AccountAvatarController -> destroy', function () {
    beforeEach(function () {
        Cache::flush();
        Storage::fake('users');
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to delete the auth avatar', function () {
            $user = User::factory()->withAvatar()->create();
            Storage::disk('users')->put($user->avatar_path, 'fake');

            deleteJson(route('account.avatar.destroy'))
                ->assertUnauthorized();

            $user->refresh();
            expect($user->avatar_path)->not->toBeNull();
            Storage::disk('users')->assertExists($user->avatar_path);
        });

        it('fails if a super-admin tries to delete the auth avatar', function () {
            $superAdmin = User::where('email', config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            deleteJson(route('account.avatar.destroy'))
                ->assertForbidden();
        });

        it('allows authenticated users to delete their own auth avatar', function ($user) {
            $avatarPath = $user->avatar_path;

            Storage::disk('users')->put($avatarPath, 'fake');
            Sanctum::actingAs($user);

            deleteJson(route('account.avatar.destroy'))
                ->assertNoContent();

            $user->refresh();
            expect($user->avatar_path)->toBeNull();
            Storage::disk('users')->assertMissing($avatarPath);
        })->with([
            'student'            => fn() => User::factory()->withAvatar()->create(),
            'teacher'            => fn() => User::factory()->teacher()->withAvatar()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->withAvatar()->create(),
            'admin'              => fn() => User::factory()->admin()->withAvatar()->create(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the user cache when an auth avatar is deleted', function () {
            $user = User::factory()->withAvatar()->create();
            Sanctum::actingAs($user);

            $page = 1;
            $cacheKey = "teachers:page:{$page}";
            $tags = [config('cache.tags.teacher_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.teacher'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            deleteJson(route('account.avatar.destroy'))
                ->assertNoContent();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('user', 'account');
