<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('Account -> AccountController -> update', function () {
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
        it('fails if an unauthenticated user tries to update the account data', function () {
            patchJson(route('account.update'), ['name' => 'New Name'])
                ->assertUnauthorized();
        });

        it('fails if name is too short', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            patchJson(route('account.update'), ['name' => 'A'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });

        it('fails if name is too long', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            patchJson(route('account.update'), ['name' => str_repeat('A', 101)])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });

        it('fails if slug format is invalid', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            patchJson(route('account.update'), ['slug' => 'Invalid Slug!'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if slug is already taken', function () {
            $user = User::factory()->create();
            $anotherUser = User::factory()->create(['slug' => 'taken-slug']);
            Sanctum::actingAs($user);

            patchJson(route('account.update'), ['slug' => $anotherUser->slug])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('allows a user to keep their own current slug', function () {
            $user = User::factory()->create(['slug' => 'my-own-slug']);
            Sanctum::actingAs($user);

            patchJson(route('account.update'), [
                'name' => 'Valid Name',
                'slug' => 'my-own-slug',
            ])->assertOk();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if a super-admin tries to update their own auth data', function () {
            $superAdmin = User::where('email', config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            patchJson(route('account.update'), [
                'name' => 'New Super Name',
                'slug' => 'new-super-slug',
            ])->assertForbidden();
        });

        it('allows an authenticated user to update their own profile data', function ($user) {
            Sanctum::actingAs($user);

            $data = [
                'name' => 'Updated Name',
                'slug' => 'updated-slug',
            ];

            patchJson(route('account.update'), $data)
                ->assertOk()
                ->assertJsonStructure(['data' => accountUserJsonStructure()])
                ->assertJsonPath('data.name', $data['name'])
                ->assertJsonPath('data.slug', $data['slug']);

            expect($user->refresh()->name)->toBe($data['name'])
                ->and($user->slug)->toBe($data['slug']);
        })->with([
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('fails if a banned user tries to update their own profile data', function () {
            $bannedUser = User::factory()->banned()->create();

            Sanctum::actingAs($bannedUser);

            $data = [
                'name' => 'Updated Name',
                'slug' => 'updated-slug',
            ];

            patchJson(route('account.update'), $data)
                ->assertForbidden();

            expect($bannedUser->refresh()->name)->not->toBe($data['name'])
                ->and($bannedUser->slug)->not->toBe($data['slug']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the teacher cache when an auth teacher profile is updated', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $page = 1;
            $cacheKey = "teachers:page:{$page}";
            $tags = [config('cache.tags.teacher_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.teacher'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            patchJson(route('account.update'), [
                'name' => 'Updated Name',
                'slug' => 'updated-slug',
            ])
                ->assertOk();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('user', 'account');
