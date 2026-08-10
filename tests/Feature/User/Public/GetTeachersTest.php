<?php

declare(strict_types=1);

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

describe('Public -> TeacherController -> index', function () {
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
        it('allows any user to retrieve a list of active teachers', function (?User $user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $teachers = User::factory()->teacher()->count(3)->create();
            $teachers->each(fn (User $teacher) => Course::factory()->for($teacher, 'author')->create());

            User::factory()->teacher()->count(2)->create();

            $bannedTeacher = User::factory()->teacher()->banned()->create();
            Course::factory()->for($bannedTeacher, 'author')->create();

            $response = getJson(route('teachers.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => publicUserJsonStructure(),
                    ],
                ]);

            expect($response->json('data.*.id'))
                ->toContain(...$teachers->pluck('id')->all())
                ->not->toContain($bannedTeacher->id);
        })->with([
            'guest' => null,
            'user' => fn () => User::factory()->create(),
            'teacher' => fn () => User::factory()->teacher()->create(),
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::whereEmail(config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | filters & sorting
    |--------------------------------------------------------------------------
    */
    describe('filters & sorting', function () {
        it('filters teachers by a search string', function () {
            $teacher1 = User::factory()->teacher()->create(['name' => 'Target Teacher']);
            Course::factory()->for($teacher1, 'author')->create();

            $teacher2 = User::factory()->teacher()->create(['name' => 'Other Person']);
            Course::factory()->for($teacher2, 'author')->create();

            $searchString = substr($teacher1->name, 4);

            getJson(route('teachers.index', ['filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonFragment(['id' => $teacher1->id])
                ->assertJsonMissing(['id' => $teacher2->id]);
        });

        it('sorts teachers by created_at (desc) by default', function () {
            $oldTeacher = User::factory()->teacher()->create();
            Course::factory()->for($oldTeacher, 'author')->create();
            DB::table('users')->where('id', $oldTeacher->id)->update(['created_at' => now()->subDays(3)]);

            $newTeacher = User::factory()->teacher()->create();
            Course::factory()->for($newTeacher, 'author')->create();
            DB::table('users')->where('id', $newTeacher->id)->update(['created_at' => now()->subDay()]);

            $response = getJson(route('teachers.index'))->assertOk();
            $ids = collect($response->json('data'))->pluck('id')->all();

            expect(array_search($newTeacher->id, $ids))->toBeLessThan(array_search($oldTeacher->id, $ids));
        });

        it('sorts teachers by created_at (asc and desc)', function () {
            $oldTeacher = User::factory()->teacher()->create();
            Course::factory()->for($oldTeacher, 'author')->create();
            DB::table('users')->where('id', $oldTeacher->id)->update(['created_at' => now()->subDays(3)]);

            $newTeacher = User::factory()->teacher()->create();
            Course::factory()->for($newTeacher, 'author')->create();
            DB::table('users')->where('id', $newTeacher->id)->update(['created_at' => now()->subDay()]);

            $ascResponse = getJson(route('teachers.index', ['sort' => 'created_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldTeacher->id, $ascIds))->toBeLessThan(array_search($newTeacher->id, $ascIds));

            $descResponse = getJson(route('teachers.index', ['sort' => '-created_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newTeacher->id, $descIds))->toBeLessThan(array_search($oldTeacher->id, $descIds));
        });

        it('sorts teachers by name (asc and desc)', function () {
            $teacherA = User::factory()->teacher()->create(['name' => 'Alpha Teacher']);
            Course::factory()->for($teacherA, 'author')->create();

            $teacherB = User::factory()->teacher()->create(['name' => 'Beta Teacher']);
            Course::factory()->for($teacherB, 'author')->create();

            $ascResponse = getJson(route('teachers.index', ['sort' => 'name']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($teacherA->id, $ascIds))->toBeLessThan(array_search($teacherB->id, $ascIds));

            $descResponse = getJson(route('teachers.index', ['sort' => '-name']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($teacherB->id, $descIds))->toBeLessThan(array_search($teacherA->id, $descIds));
        });

        it('sorts teachers by courses_count (asc and desc)', function () {
            $teacherWithFew = User::factory()->teacher()->create();
            Course::factory()->count(2)->for($teacherWithFew, 'author')->create();

            $teacherWithMany = User::factory()->teacher()->create();
            Course::factory()->count(5)->for($teacherWithMany, 'author')->create();

            $ascResponse = getJson(route('teachers.index', ['sort' => 'courses_count']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($teacherWithFew->id, $ascIds))->toBeLessThan(array_search($teacherWithMany->id, $ascIds));

            $descResponse = getJson(route('teachers.index', ['sort' => '-courses_count']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($teacherWithMany->id, $descIds))->toBeLessThan(array_search($teacherWithFew->id, $descIds));
        });

        it('returns empty data when no teachers match the search', function () {
            $teacher = User::factory()->teacher()->create();
            Course::factory()->for($teacher, 'author')->create();

            getJson(route('teachers.index', ['filter[search]' => 'non-existent-teacher-name']))
                ->assertOk()
                ->assertJsonCount(0, 'data');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('stores the user list in the cache after the first request', function () {
            $teacher = User::factory()->teacher()->create();
            Course::factory()->for($teacher, 'author')->create();

            $page = 1;
            $cacheKey = "teachers:page:{$page}";
            $tags = [config('cache.tags.teacher_list')];

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();

            getJson(route('teachers.index'))->assertOk();

            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();
        });

        it('returns data from the cache instead of the database on subsequent requests', function () {
            $oldName = 'Cached User Name';
            $teacher = User::factory()->teacher()->create(['name' => $oldName]);
            Course::factory()->for($teacher, 'author')->create();

            getJson(route('teachers.index'))->assertOk();

            $newName = 'Updated User Name';
            DB::table('users')->where('name', $oldName)->update(['name' => $newName]);

            getJson(route('teachers.index'))
                ->assertOk()
                ->assertJsonFragment(['name' => $oldName])
                ->assertJsonMissing(['name' => $newName]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | pagination
    |--------------------------------------------------------------------------
    */
    describe('pagination', function () {
        it('returns a paginated list of teachers', function () {
            $teachers = User::factory()->count(15)->create();
            foreach ($teachers as $teacher) {
                Course::factory()->for($teacher, 'author')->create();
            }

            getJson(route('teachers.index'))
                ->assertOk()
                ->assertJsonStructure(paginationJsonStructure());
        });
    });
})->group('user', 'public');
