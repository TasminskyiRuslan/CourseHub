<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('Admin -> LessonController -> index', function () {
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
        it('fails if the course does not exist', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            getJson(route('admin.courses.lessons.index', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to retrieve the lessons', function () {
            $course = Course::factory()->create();

            getJson(route('admin.courses.lessons.index', $course))
                ->assertUnauthorized();
        });

        it('fails if a user without permissions tries to retrieve the lessons', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            getJson(route('admin.courses.lessons.index', $course))
                ->assertForbidden();
        })->with([
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
        ]);

        it('allows a user with permission to retrieve lessons of the course', function ($userClosure, $courseClosure) {
            $user = $userClosure();
            Sanctum::actingAs($user);

            $course = $courseClosure();

            $lessons = Lesson::factory()->count(2)->for($course, 'course')->create();

            $response = getJson(route('admin.courses.lessons.index', $course))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => adminLessonJsonStructure($course->type)
                    ]
                ]);

            $responseDataIds = collect($response->json('data'))->pluck('id');
            foreach ($lessons as $lesson) {
                expect($responseDataIds)->toContain($lesson->id);
            }
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published' => fn() => Course::factory()->create(),
            'unpublished' => fn() => Course::factory()->unpublished()->create(),
            'banned' => fn() => Course::factory()->banned()->create(),
        ]);

        it('fails if a banned user tries to retrieve lessons of the course', function () {
            $bannedAuthor = User::factory()->admin()->banned()->create();
            $course = Course::factory()->for($bannedAuthor, 'author')->create();
            $lessons = Lesson::factory()->count(2)->for($course, 'course')->create();

            Sanctum::actingAs($bannedAuthor);

            getJson(route('admin.courses.lessons.index', $course))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | filters & sorting
    |--------------------------------------------------------------------------
    */
    describe('filters & sorting', function () {
        it('filters lessons by a search string', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();

            $lesson1 = Lesson::factory()->for($course, 'course')->create(['title' => 'Introduction to Laravel']);
            $lesson2 = Lesson::factory()->for($course, 'course')->create(['title' => 'Advanced Vue.js']);
            $searchString = substr($lesson1->title, 4);

            getJson(route('admin.courses.lessons.index', [$course, 'filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonFragment(['id' => $lesson1->id])
                ->assertJsonMissing(['data' => [['id' => $lesson2->id]]]);
        });

        it('filters lessons by trashed records (soft deletes)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();

            $activeLesson = Lesson::factory()->for($course, 'course')->create();
            $trashedLesson = Lesson::factory()->for($course, 'course')->create();
            $trashedLesson->delete();

            getJson(route('admin.courses.lessons.index', $course))
                ->assertOk()
                ->assertJsonFragment(['id' => $activeLesson->id])
                ->assertJsonMissing(['data' => [['id' => $trashedLesson->id]]]);

            getJson(route('admin.courses.lessons.index', [$course, 'filter[trashed]' => 'with']))
                ->assertOk()
                ->assertJsonFragment(['id' => $activeLesson->id])
                ->assertJsonFragment(['id' => $trashedLesson->id]);

            getJson(route('admin.courses.lessons.index', [$course, 'filter[trashed]' => 'only']))
                ->assertOk()
                ->assertJsonFragment(['id' => $trashedLesson->id])
                ->assertJsonMissing(['data' => [['id' => $activeLesson->id]]]);
        });

        it('sorts lessons by position (asc) by default', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();

            $secondLesson = Lesson::factory()->for($course, 'course')->create(['position' => 2]);
            $firstLesson = Lesson::factory()->for($course, 'course')->create(['position' => 1]);

            $response = getJson(route('admin.courses.lessons.index', $course))->assertOk();
            $ids = collect($response->json('data'))->pluck('id')->all();

            expect(array_search($firstLesson->id, $ids))->toBeLessThan(array_search($secondLesson->id, $ids));
        });

        it('sorts lessons by position (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();

            $secondLesson = Lesson::factory()->for($course, 'course')->create(['position' => 2]);
            $firstLesson = Lesson::factory()->for($course, 'course')->create(['position' => 1]);

            $ascResponse = getJson(route('admin.courses.lessons.index', [$course, 'sort' => 'position']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($firstLesson->id, $ascIds))->toBeLessThan(array_search($secondLesson->id, $ascIds));

            $descResponse = getJson(route('admin.courses.lessons.index', [$course, 'sort' => '-position']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($secondLesson->id, $descIds))->toBeLessThan(array_search($firstLesson->id, $descIds));
        });

        it('sorts lessons by title (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();

            $lessonA = Lesson::factory()->for($course, 'course')->create(['title' => 'Alpha Lesson']);
            $lessonB = Lesson::factory()->for($course, 'course')->create(['title' => 'Beta Lesson']);

            $ascResponse = getJson(route('admin.courses.lessons.index', [$course, 'sort' => 'title']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($lessonA->id, $ascIds))->toBeLessThan(array_search($lessonB->id, $ascIds));

            $descResponse = getJson(route('admin.courses.lessons.index', [$course, 'sort' => '-title']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($lessonB->id, $descIds))->toBeLessThan(array_search($lessonA->id, $descIds));
        });

        it('sorts lessons by created_at (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();

            $oldLesson = Lesson::factory()->for($course, 'course')->create();
            DB::table('lessons')->where('id', $oldLesson->id)->update(['created_at' => now()->subDays(3)]);

            $newLesson = Lesson::factory()->for($course, 'course')->create();
            DB::table('lessons')->where('id', $newLesson->id)->update(['created_at' => now()->subDay()]);

            $ascResponse = getJson(route('admin.courses.lessons.index', [$course, 'sort' => 'created_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldLesson->id, $ascIds))->toBeLessThan(array_search($newLesson->id, $ascIds));

            $descResponse = getJson(route('admin.courses.lessons.index', [$course, 'sort' => '-created_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newLesson->id, $descIds))->toBeLessThan(array_search($oldLesson->id, $descIds));
        });

        it('sorts lessons by deleted_at (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();

            $oldDeleted = Lesson::factory()->for($course, 'course')->create();
            $oldDeleted->delete();
            DB::table('lessons')->where('id', $oldDeleted->id)->update(['deleted_at' => now()->subDays(5)]);

            $newDeleted = Lesson::factory()->for($course, 'course')->create();
            $newDeleted->delete();
            DB::table('lessons')->where('id', $newDeleted->id)->update(['deleted_at' => now()->subDay()]);

            $ascResponse = getJson(route('admin.courses.lessons.index', [$course, 'filter[trashed]' => 'only', 'sort' => 'deleted_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldDeleted->id, $ascIds))->toBeLessThan(array_search($newDeleted->id, $ascIds));

            $descResponse = getJson(route('admin.courses.lessons.index', [$course, 'filter[trashed]' => 'only', 'sort' => '-deleted_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newDeleted->id, $descIds))->toBeLessThan(array_search($oldDeleted->id, $descIds));
        });

        it('returns empty data when no lessons match the search', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();
            Lesson::factory()->for($course, 'course')->create();

            getJson(route('admin.courses.lessons.index', [$course, 'filter[search]' => 'non-existent-lesson-title']))
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
        it('returns a paginated list of lessons', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();
            Lesson::factory()->count(3)->for($course, 'course')->create();

            getJson(route('admin.courses.lessons.index', $course))
                ->assertOk()
                ->assertJsonStructure(paginationJsonStructure());
        });
    });
})->group('lesson', 'admin');
