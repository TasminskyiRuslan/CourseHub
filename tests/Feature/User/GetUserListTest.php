<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

describe('UserController -> index', function () {
    beforeEach(function () {
        Cache::flush();
        seed(RolesAndPermissionsSeeder::class);
        seed(SuperAdminUserSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if unauthenticated user tries to retrieve users', function () {
            $users = User::factory()->count(3)->create();

            getJson(route('user.index'))
                ->assertUnauthorized();
        });

        it('fails if users without permissions tries to retrieve users', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $users = User::factory()->count(3)->create();

            getJson(route('user.index'))
                ->assertForbidden();
        })->with([
            'unverified' => fn() => User::factory()->unverified()->create(),
            'student' => fn() => User::factory()->student()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows users with permissions to retrieve users', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $users = User::factory()->count(3)->create();

            getJson(route('user.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | filters & sorting
    |--------------------------------------------------------------------------
    */
    describe('filters & sorting', function () {
        it('filters users by a search string', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $user1 = User::factory()->create(['name' => 'Target User', 'email' => 'target@example.com']);
            $user2 = User::factory()->create(['name' => 'Other Person', 'email' => 'other@test.com']);
            $searchString = substr($user1->name, 7);

            getJson(route('user.index', ['filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonFragment(['id' => $user1->id])
                ->assertJsonMissing(['id' => $user2->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
        });

        it('filters users by role', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $teacher = User::factory()->teacher()->create();
            $student = User::factory()->student()->create();

            getJson(route('user.index', ['filter[role]' => 'teacher']))
                ->assertOk()
                ->assertJsonFragment(['id' => $teacher->id])
                ->assertJsonMissing(['id' => $student->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
        });

        it('filters users by verified status', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $verified = User::factory()->create();
            $unverified = User::factory()->unverified()->create();

            getJson(route('user.index', ['filter[verified]' => true]))
                ->assertOk()
                ->assertJsonFragment(['id' => $verified->id])
                ->assertJsonMissing(['id' => $unverified->id]);

            getJson(route('user.index', ['filter[verified]' => false]))
                ->assertOk()
                ->assertJsonFragment(['id' => $unverified->id])
                ->assertJsonMissing(['id' => $verified->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
        });

        it('filters users by banned status', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $banned = User::factory()->create(['banned_at' => now()->subDay()]);
            $active = User::factory()->create(['banned_at' => null]);

            getJson(route('user.index', ['filter[banned]' => true]))
                ->assertOk()
                ->assertJsonFragment(['id' => $banned->id])
                ->assertJsonMissing(['id' => $active->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);

            getJson(route('user.index', ['filter[banned]' => false]))
                ->assertOk()
                ->assertJsonFragment(['id' => $active->id])
                ->assertJsonMissing(['id' => $banned->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
        });

        it('filters users by trashed status', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $user = User::factory()->create();
            $deletedUser = User::factory()->create();
            $deletedUser->delete();

            getJson(route('user.index', ['filter[trashed]' => 'only']))
                ->assertOk()
                ->assertJsonFragment(['id' => $deletedUser->id])
                ->assertJsonMissing(['id' => $user->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);

            getJson(route('user.index', ['filter[trashed]' => 'with']))
                ->assertOk()
                ->assertJsonFragment(['id' => $user->id])
                ->assertJsonFragment(['id' => $deletedUser->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
        });

        it('sorts users by created_at (desc) by default', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $oldUser = User::factory()->create();
            $oldUser->setCreatedAt(now()->subDays(2))->save();
            $newUser = User::factory()->create();
            $newUser->setCreatedAt(now()->subDay())->save();

            getJson(route('user.index'))
                ->assertOk()
                ->assertSeeInOrder([$newUser->id, $oldUser->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
        });

        it('sorts users by created_at (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $oldUser = User::factory()->create();
            $oldUser->setCreatedAt(now()->subDays(2))->save();
            $newUser = User::factory()->create();
            $newUser->setCreatedAt(now()->subDay())->save();

            getJson(route('user.index', ['sort' => 'created_at']))
                ->assertOk()
                ->assertSeeInOrder([$oldUser->id, $newUser->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
            getJson(route('user.index', ['sort' => '-created_at']))
                ->assertOk()
                ->assertSeeInOrder([$newUser->id, $oldUser->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
        });

        it('sorts users by name (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $userA = User::factory()->create(['name' => 'UserA']);
            $userB = User::factory()->create(['name' => 'UserB']);
            $userC = User::factory()->create(['name' => 'UserC']);

            getJson(route('user.index', ['sort' => 'name']))
                ->assertOk()
                ->assertSeeInOrder([$userA->id, $userB->id, $userC->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
            getJson(route('user.index', ['sort' => '-name']))
                ->assertOk()
                ->assertSeeInOrder([$userC->id, $userB->id, $userA->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
        });

        it('sorts users by email_verified_at (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $oldVerifiedUser = User::factory()->create();
            $oldVerifiedUser->forceFill(['email_verified_at' => now()->subDays(2)])->save();
            $newVerifiedUser = User::factory()->create();
            $newVerifiedUser->forceFill(['email_verified_at' => now()->subDay()])->save();

            getJson(route('user.index', ['sort' => 'email_verified_at']))
                ->assertOk()
                ->assertSeeInOrder([$oldVerifiedUser->id, $newVerifiedUser->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
            getJson(route('user.index', ['sort' => '-email_verified_at']))
                ->assertOk()
                ->assertSeeInOrder([$newVerifiedUser->id, $oldVerifiedUser->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
        });

        it('sorts users by banned_at (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $oldBannedUser = User::factory()->create(['banned_at' => now()->subDays(2)]);
            $newBannedUser = User::factory()->create(['banned_at' => now()->subDay()]);

            getJson(route('user.index', ['sort' => 'banned_at']))
                ->assertOk()
                ->assertSeeInOrder([$oldBannedUser->id, $newBannedUser->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
            getJson(route('user.index', ['sort' => '-banned_at']))
                ->assertOk()
                ->assertSeeInOrder([$newBannedUser->id, $oldBannedUser->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | pagination
    |--------------------------------------------------------------------------
    */
    describe('pagination', function () {
        it('returns a paginated list of users', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            User::factory()->count(15)->create();

            getJson(route('user.index'))
                ->assertOk()
                ->assertJsonStructure(paginationJsonStructure());
        });
    });
})->group('user');
