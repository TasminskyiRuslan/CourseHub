<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('Account -> AccountAvatarController -> update', function () {

    beforeEach(function () {
        Cache::flush();
        Storage::fake('users');
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the required fields are missing', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            postJson(route('account.avatar.update'), ['_method' => 'PUT'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['avatar']);
        });

        it('fails if the avatar is not a file', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            postJson(route('account.avatar.update'), avatarPayload([
                'avatar' => 'not-a-file',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['avatar']);
        });

        it('fails if the file is not an image', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            postJson(route('account.avatar.update'), avatarPayload([
                'avatar' => UploadedFile::fake()->create('document.pdf'),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['avatar']);
        });

        it('fails if the avatar exceeds the 2048KB size limit', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            postJson(route('account.avatar.update'), avatarPayload([
                'avatar' => UploadedFile::fake()->create('avatar.jpg')->size(2049),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['avatar']);
        });

        it('succeeds if avatar uploads with all allowed extensions', function ($ext) {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            postJson(route('account.avatar.update'), avatarPayload([
                'avatar' => UploadedFile::fake()->image("avatar.$ext"),
            ]))
                ->assertOk()
                ->assertJsonStructure(['data' => accountUserJsonStructure()]);

            $user->refresh();
            expect($user->avatar_path)->not->toBeNull();
            Storage::disk('users')->assertExists($user->avatar_path);
        })->with(['jpg', 'jpeg', 'png']);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to update the auth image', function () {
            postJson(route('account.avatar.update'), avatarPayload())
                ->assertUnauthorized();
        });

        it('fails if a super-admin tries to update the auth avatar', function () {
            $superAdmin = User::where('email', config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            postJson(route('account.avatar.update'), avatarPayload())
                ->assertForbidden();

            $superAdmin->refresh();
            expect($superAdmin->avatar_path)->toBeNull();
        });

        it('allows an authenticated user with any role to update their own avatar', function ($user) {
            Sanctum::actingAs($user);

            $oldAvatarPath = $user->avatar_path;

            postJson(route('account.avatar.update'), avatarPayload())
                ->assertOk()
                ->assertJsonStructure(['data' => accountUserJsonStructure()]);

            $user->refresh();
            expect($user->avatar_path)->not->toBeNull();
            Storage::disk('users')->assertExists($user->avatar_path);
            Storage::disk('users')->assertMissing($oldAvatarPath);
        })->with([
            'user' => fn() => User::factory()->withAvatar()->create(),
            'teacher' => fn() => User::factory()->withAvatar()->teacher()->create(),
            'unverified teacher' => fn() => User::factory()->withAvatar()->teacher()->unverified()->create(),
            'admin' => fn() => User::factory()->withAvatar()->admin()->create(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the user cache when an auth avatar is updated', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $page = 1;
            $cacheKey = "teachers:page:{$page}";
            $tags = [config('cache.tags.teacher_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.teacher'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            postJson(route('account.avatar.update'), avatarPayload())
                ->assertOk();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('user', 'account');
