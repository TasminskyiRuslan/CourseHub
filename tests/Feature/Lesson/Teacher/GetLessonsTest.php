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

describe('Teacher -> LessonController -> index', function () {
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
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            getJson(route('teacher.course.lesson.index', 'non-existing-slug'))
                ->assertNotFound();
        });

        it('fails if a non-author user tries to retrieve the lessons', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            getJson(route('teacher.course.lesson.index', $course))
                ->assertNotFound();
        })->with([
            'other teacher' => fn() => User::factory()->teacher()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to retrieve the lessons', function () {
            $course = Course::factory()->create();

            getJson(route('teacher.course.lesson.index', $course))
                ->assertUnauthorized();
        });

        it('fails if a user without permissions tries to retrieve the lessons', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            getJson(route('teacher.course.lesson.index', $course))
                ->assertForbidden();
        })->with([
            'user' => fn() => User::factory()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('allows authors to retrieve lessons of their own course', function ($userClosure, $courseClosure) {
            $user = $userClosure();
            Sanctum::actingAs($user);

            $courseInnerClosure = $courseClosure();
            $course = $courseInnerClosure($user);

            $lessons = Lesson::factory()->count(2)->for($course)->create();

            $response = getJson(route('teacher.course.lesson.index', $course))
                ->assertOk()
                ->assertJsonCount(2, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => teacherLessonJsonStructure($course->type)
                    ]
                ]);

            $responseDataIds = collect($response->json('data'))->pluck('id');
            foreach ($lessons as $lesson) {
                expect($responseDataIds)->toContain($lesson->id);
            }
        })->with([
            'teacher' => fn() => User::factory()->teacher()->create(),
        ])->with([
            'published' => fn() => fn($author) => Course::factory()->for($author, 'author')->create(),
            'unpublished' => fn() => fn($author) => Course::factory()->unpublished()->for($author, 'author')->create(),
            'banned' => fn() => fn($author) => Course::factory()->banned()->for($author, 'author')->create(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | filters & sorting
    |--------------------------------------------------------------------------
    */
    describe('filters & sorting', function () {
        it('filters lessons by a search string', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();

            $lesson1 = Lesson::factory()->for($course)->create(['title' => 'Introduction to Laravel']);
            $lesson2 = Lesson::factory()->for($course)->create(['title' => 'Advanced Vue.js']);
            $searchString = substr($lesson1->title, 4);

            getJson(route('teacher.course.lesson.index', [$course, 'filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonFragment(['id' => $lesson1->id])
                ->assertJsonMissing(['data' => [['id' => $lesson2->id]]]);
        });

        it('sorts lessons by position (asc) by default', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();

            $secondLesson = Lesson::factory()->for($course)->create(['position' => 2]);
            $firstLesson = Lesson::factory()->for($course)->create(['position' => 1]);

            $response = getJson(route('teacher.course.lesson.index', $course))->assertOk();
            $ids = collect($response->json('data'))->pluck('id')->all();

            expect(array_search($firstLesson->id, $ids))->toBeLessThan(array_search($secondLesson->id, $ids));
        });

        it('sorts lessons by position (asc and desc)', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();

            $secondLesson = Lesson::factory()->for($course)->create(['position' => 2]);
            $firstLesson = Lesson::factory()->for($course)->create(['position' => 1]);

            $ascResponse = getJson(route('teacher.course.lesson.index', [$course, 'sort' => 'position']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($firstLesson->id, $ascIds))->toBeLessThan(array_search($secondLesson->id, $ascIds));

            $descResponse = getJson(route('teacher.course.lesson.index', [$course, 'sort' => '-position']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($secondLesson->id, $descIds))->toBeLessThan(array_search($firstLesson->id, $descIds));
        });

        it('sorts lessons by title (asc and desc)', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();

            $lessonA = Lesson::factory()->for($course)->create(['title' => 'Alpha Lesson']);
            $lessonB = Lesson::factory()->for($course)->create(['title' => 'Beta Lesson']);

            $ascResponse = getJson(route('teacher.course.lesson.index', [$course, 'sort' => 'title']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($lessonA->id, $ascIds))->toBeLessThan(array_search($lessonB->id, $ascIds));

            $descResponse = getJson(route('teacher.course.lesson.index', [$course, 'sort' => '-title']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($lessonB->id, $descIds))->toBeLessThan(array_search($lessonA->id, $descIds));
        });

        it('sorts lessons by created_at (asc and desc)', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();

            $oldLesson = Lesson::factory()->for($course)->create();
            DB::table('lessons')->where('id', $oldLesson->id)->update(['created_at' => now()->subDays(3)]);

            $newLesson = Lesson::factory()->for($course)->create();
            DB::table('lessons')->where('id', $newLesson->id)->update(['created_at' => now()->subDay()]);

            $ascResponse = getJson(route('teacher.course.lesson.index', [$course, 'sort' => 'created_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldLesson->id, $ascIds))->toBeLessThan(array_search($newLesson->id, $ascIds));

            $descResponse = getJson(route('teacher.course.lesson.index', [$course, 'sort' => '-created_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newLesson->id, $descIds))->toBeLessThan(array_search($oldLesson->id, $descIds));
        });

        it('returns empty data when no lessons match the search', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();
            Lesson::factory()->for($course)->create();

            getJson(route('teacher.course.lesson.index', [$course, 'filter[search]' => 'non-existent-lesson-title']))
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
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);
            $course = Course::factory()->for($teacher, 'author')->create();

            Lesson::factory()->count(3)->for($course)->create();

            getJson(route('teacher.course.lesson.index', $course))
                ->assertOk()
                ->assertJsonStructure(paginationJsonStructure());
        });
    });
})->group('lesson', 'teacher');
