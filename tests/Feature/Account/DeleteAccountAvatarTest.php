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

describe('AccountAvatarController -> destroy', function () {
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
        it('fails if an unauthenticated user tries to delete the account avatar', function () {
            $account = User::factory()->withAvatar()->create();
            Storage::disk('users')->put($account->avatar_path, 'fake');

            deleteJson(route('account.avatar.destroy'))
                ->assertUnauthorized();
            $account->refresh();
            expect($account->avatar_path)->not->toBeNull();
            Storage::disk('users')->assertExists($account->avatar_path);
        });

        it('allows authenticated users to delete their own account avatar', function ($user) {
            $avatar_path = $user->avatar_path;
            Sanctum::actingAs($user);

            deleteJson(route('account.avatar.destroy'))
                ->assertNoContent();

            $user->refresh();
            expect($user->avatar_path)->toBeNull();
            Storage::disk('users')->assertMissing($avatar_path);
        })->with([
            'student' => fn() => User::factory()->withAvatar()->student()->create(),
            'teacher' => fn() => User::factory()->withAvatar()->teacher()->create(),
            'admin' => fn() => User::factory()->withAvatar()->admin()->create(),
        ]);

        it('fails if a super-admin tries to delete the account avatar', function () {
            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            deleteJson(route('account.avatar.destroy'))
                ->assertUnprocessable();
        });
    });
})->group('auth');
