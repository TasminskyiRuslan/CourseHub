<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

describe('Admin -> UserController -> index', function () {
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
            User::factory()->count(3)->create();

            getJson(route('admin.users.index'))
                ->assertUnauthorized();
        });

        it('fails if users without permissions tries to retrieve users', function (?User $user) {
            Sanctum::actingAs($user);

            User::factory()->count(3)->create();

            getJson(route('admin.users.index'))
                ->assertForbidden();
        })->with([
            'user' => fn () => User::factory()->create(),
            'teacher' => fn () => User::factory()->teacher()->create(),
        ]);

        it('allows users with permissions to retrieve all users', function (?User $user) {
            Sanctum::actingAs($user);

            $activeUsers = User::factory()->count(2)->create();
            $bannedUsers = User::factory()->count(2)->banned()->create();

            $response = getJson(route('admin.users.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => adminUserJsonStructure(),
                    ],
                ]);

            $createdUserIds = $activeUsers->merge($bannedUsers)->pluck('id')->all();

            expect($response->json('data.*.id'))
                ->toContain(...$createdUserIds);
        })->with([
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::whereEmail(config('super-admin.email'))->first(),
        ]);

        it('fails if a banned user tries to retrieve all users', function () {
            $bannedUser = User::factory()->admin()->banned()->create();

            Sanctum::actingAs($bannedUser);

            getJson(route('admin.users.index'))
                ->assertForbidden();
        });
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

            getJson(route('admin.users.index', ['filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonFragment(['id' => $user1->id])
                ->assertJsonMissing(['id' => $user2->id]);
        });

        it('filters users by role', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $teacher = User::factory()->teacher()->create();
            $user = User::factory()->create();

            getJson(route('admin.users.index', ['filter[role]' => UserRole::TEACHER->value]))
                ->assertOk()
                ->assertJsonFragment(['id' => $teacher->id])
                ->assertJsonMissing(['id' => $user->id]);
        });

        it('filters users by verified status', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $verified = User::factory()->create();
            $unverified = User::factory()->unverified()->create();

            getJson(route('admin.users.index', ['filter[verified]' => true]))
                ->assertOk()
                ->assertJsonFragment(['id' => $verified->id])
                ->assertJsonMissing(['id' => $unverified->id]);

            getJson(route('admin.users.index', ['filter[verified]' => false]))
                ->assertOk()
                ->assertJsonFragment(['id' => $unverified->id])
                ->assertJsonMissing(['id' => $verified->id]);
        });

        it('filters users by banned status', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $banned = User::factory()->create(['banned_at' => now()->subDay()]);
            $active = User::factory()->create(['banned_at' => null]);

            getJson(route('admin.users.index', ['filter[banned]' => true]))
                ->assertOk()
                ->assertJsonFragment(['id' => $banned->id])
                ->assertJsonMissing(['id' => $active->id]);

            getJson(route('admin.users.index', ['filter[banned]' => false]))
                ->assertOk()
                ->assertJsonFragment(['id' => $active->id])
                ->assertJsonMissing(['id' => $banned->id]);
        });

        it('filters users by trashed status', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $user = User::factory()->create();
            $deletedUser = User::factory()->create();
            $deletedUser->delete();

            getJson(route('admin.users.index', ['filter[trashed]' => 'only']))
                ->assertOk()
                ->assertJsonFragment(['id' => $deletedUser->id])
                ->assertJsonMissing(['id' => $user->id]);

            getJson(route('admin.users.index', ['filter[trashed]' => 'with']))
                ->assertOk()
                ->assertJsonFragment(['id' => $user->id])
                ->assertJsonFragment(['id' => $deletedUser->id]);
        });

        it('sorts users by created_at (desc) by default', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $oldUser = User::factory()->create();
            DB::table('users')->where('id', $oldUser->id)->update(['created_at' => now()->subDays(3)]);

            $newUser = User::factory()->create();
            DB::table('users')->where('id', $newUser->id)->update(['created_at' => now()->subDay()]);

            $response = getJson(route('admin.users.index'))->assertOk();
            $ids = collect($response->json('data'))->pluck('id')->all();

            expect(array_search($newUser->id, $ids))->toBeLessThan(array_search($oldUser->id, $ids));
        });

        it('sorts users by name (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $firstAlphabeticalUser = User::factory()->create(['name' => 'Aaron']);
            $lastAlphabeticalUser = User::factory()->create(['name' => 'Zachary']);

            $ascResponse = getJson(route('admin.users.index', ['sort' => 'name']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($firstAlphabeticalUser->id, $ascIds))->toBeLessThan(array_search($lastAlphabeticalUser->id, $ascIds));

            $descResponse = getJson(route('admin.users.index', ['sort' => '-name']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($lastAlphabeticalUser->id, $descIds))->toBeLessThan(array_search($firstAlphabeticalUser->id, $descIds));
        });

        it('sorts users by created_at (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $oldUser = User::factory()->create();
            DB::table('users')->where('id', $oldUser->id)->update(['created_at' => now()->subDays(3)]);

            $newUser = User::factory()->create();
            DB::table('users')->where('id', $newUser->id)->update(['created_at' => now()->subDay()]);

            $ascResponse = getJson(route('admin.users.index', ['sort' => 'created_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldUser->id, $ascIds))->toBeLessThan(array_search($newUser->id, $ascIds));

            $descResponse = getJson(route('admin.users.index', ['sort' => '-created_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newUser->id, $descIds))->toBeLessThan(array_search($oldUser->id, $descIds));
        });

        it('sorts users by email_verified_at (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $oldVerified = User::factory()->create();
            DB::table('users')->where('id', $oldVerified->id)->update(['email_verified_at' => now()->subDays(3)]);

            $newVerified = User::factory()->create();
            DB::table('users')->where('id', $newVerified->id)->update(['email_verified_at' => now()->subDay()]);

            $ascResponse = getJson(route('admin.users.index', ['sort' => 'email_verified_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldVerified->id, $ascIds))->toBeLessThan(array_search($newVerified->id, $ascIds));

            $descResponse = getJson(route('admin.users.index', ['sort' => '-email_verified_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newVerified->id, $descIds))->toBeLessThan(array_search($oldVerified->id, $descIds));
        });

        it('sorts users by banned_at (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $oldBanned = User::factory()->banned()->create();
            DB::table('users')->where('id', $oldBanned->id)->update(['banned_at' => now()->subDays(3)]);

            $newBanned = User::factory()->banned()->create();
            DB::table('users')->where('id', $newBanned->id)->update(['banned_at' => now()->subDay()]);

            $ascResponse = getJson(route('admin.users.index', ['sort' => 'banned_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldBanned->id, $ascIds))->toBeLessThan(array_search($newBanned->id, $ascIds));

            $descResponse = getJson(route('admin.users.index', ['sort' => '-banned_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newBanned->id, $descIds))->toBeLessThan(array_search($oldBanned->id, $descIds));
        });

        it('sorts teachers by courses_count (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $teacherWithFew = User::factory()->teacher()->create();
            Course::factory()->count(2)->for($teacherWithFew, 'author')->create();

            $teacherWithMany = User::factory()->teacher()->create();
            Course::factory()->count(5)->for($teacherWithMany, 'author')->create();

            $ascResponse = getJson(route('admin.users.index', ['sort' => 'courses_count']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($teacherWithFew->id, $ascIds))->toBeLessThan(array_search($teacherWithMany->id, $ascIds));

            $descResponse = getJson(route('admin.users.index', ['sort' => '-courses_count']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($teacherWithMany->id, $descIds))->toBeLessThan(array_search($teacherWithFew->id, $descIds));
        });

        it('sorts users by deleted_at (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $oldDeleted = User::factory()->create();
            $oldDeleted->delete();
            DB::table('users')->where('id', $oldDeleted->id)->update(['deleted_at' => now()->subDays(3)]);

            $newDeleted = User::factory()->create();
            $newDeleted->delete();
            DB::table('users')->where('id', $newDeleted->id)->update(['deleted_at' => now()->subDay()]);

            $ascResponse = getJson(route('admin.users.index', ['filter[trashed]' => 'only', 'sort' => 'deleted_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldDeleted->id, $ascIds))->toBeLessThan(array_search($newDeleted->id, $ascIds));

            $descResponse = getJson(route('admin.users.index', ['filter[trashed]' => 'only', 'sort' => '-deleted_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newDeleted->id, $descIds))->toBeLessThan(array_search($oldDeleted->id, $descIds));
        });

        it('returns empty data when no users match the search', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            getJson(route('admin.users.index', ['filter[search]' => 'non-existent-user-name']))
                ->assertOk()
                ->assertJsonCount(0, 'data');
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

            getJson(route('admin.users.index'))
                ->assertOk()
                ->assertJsonStructure(paginationJsonStructure());
        });
    });
})->group('user', 'admin');
