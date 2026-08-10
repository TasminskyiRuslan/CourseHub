<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('Student -> LessonController -> index', function () {
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
            $student = User::factory()->create();
            Sanctum::actingAs($student);

            getJson(route('student.courses.lessons.index', 'non-existing-slug'))
                ->assertNotFound();
        });

        it('fails if a non-enrolled user tries to retrieve the lessons', function (?User $user) {
            Sanctum::actingAs($user);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();

            getJson(route('student.courses.lessons.index', $course))
                ->assertNotFound();
        })->with([
            'another student' => fn () => User::factory()->create(),
            'teacher' => fn () => User::factory()->teacher()->create(),
            'unverified student' => fn () => User::factory()->unverified()->create(),
            'unverified teacher' => fn () => User::factory()->teacher()->unverified()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to retrieve the lessons', function () {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();

            getJson(route('student.courses.lessons.index', $course))
                ->assertUnauthorized();
        });

        it('allows enrolled students to retrieve lessons of their course', function (?User $user, Course $course) {
            Sanctum::actingAs($user);

            $user->enrolledCourses()->attach($course);

            $lessons = Lesson::factory()->count(2)->for($course)->create();

            $response = getJson(route('student.courses.lessons.index', $course))
                ->assertOk()
                ->assertJsonCount(2, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => studentLessonJsonStructure($course->type),
                    ],
                ]);

            expect($response->json('data.*.id'))
                ->toContain(...$lessons->pluck('id')->all());
        })->with([
            'student' => fn () => User::factory()->create(),
            'teacher' => fn () => User::factory()->teacher()->create(),
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published course' => fn () => Course::factory()->create(),
        ]);

        it('fails if a student tries to retrieve lessons of an inactive course', function (Course $course) {
            $student = User::factory()->create();
            $student->enrolledCourses()->attach($course);

            Sanctum::actingAs($student);

            getJson(route('student.courses.lessons.index', $course))
                ->assertNotFound();
        })->with([
            'unpublished course' => fn () => Course::factory()->unpublished()->create(),
            'banned course' => fn () => Course::factory()->banned()->create(),
        ]);

        it('fails if a banned user tries to retrieve lessons of their enrolled course', function () {
            $bannedUser = User::factory()->banned()->create();
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();

            $bannedUser->enrolledCourses()->attach($course);

            Sanctum::actingAs($bannedUser);

            getJson(route('student.courses.lessons.index', $course))
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
            $student = User::factory()->create();
            Sanctum::actingAs($student);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $student->enrolledCourses()->attach($course);

            $lesson1 = Lesson::factory()->for($course)->create(['title' => 'Introduction to Laravel']);
            $lesson2 = Lesson::factory()->for($course)->create(['title' => 'Advanced Vue.js']);
            $searchString = substr($lesson1->title, 4);

            getJson(route('student.courses.lessons.index', [$course, 'filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonFragment(['id' => $lesson1->id])
                ->assertJsonMissing(['data' => [['id' => $lesson2->id]]]);
        });

        it('sorts lessons by position (asc) by default', function () {
            $student = User::factory()->create();
            Sanctum::actingAs($student);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $student->enrolledCourses()->attach($course);

            $secondLesson = Lesson::factory()->for($course)->create(['position' => 2]);
            $firstLesson = Lesson::factory()->for($course)->create(['position' => 1]);

            $response = getJson(route('student.courses.lessons.index', $course))->assertOk();
            $ids = collect($response->json('data'))->pluck('id')->all();

            expect(array_search($firstLesson->id, $ids))->toBeLessThan(array_search($secondLesson->id, $ids));
        });

        it('sorts lessons by position (asc and desc)', function () {
            $student = User::factory()->create();
            Sanctum::actingAs($student);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $student->enrolledCourses()->attach($course);

            $secondLesson = Lesson::factory()->for($course)->create(['position' => 2]);
            $firstLesson = Lesson::factory()->for($course)->create(['position' => 1]);

            $ascResponse = getJson(route('student.courses.lessons.index', [$course, 'sort' => 'position']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($firstLesson->id, $ascIds))->toBeLessThan(array_search($secondLesson->id, $ascIds));

            $descResponse = getJson(route('student.courses.lessons.index', [$course, 'sort' => '-position']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($secondLesson->id, $descIds))->toBeLessThan(array_search($firstLesson->id, $descIds));
        });

        it('sorts lessons by title (asc and desc)', function () {
            $student = User::factory()->create();
            Sanctum::actingAs($student);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $student->enrolledCourses()->attach($course);

            $lessonA = Lesson::factory()->for($course)->create(['title' => 'Alpha Lesson']);
            $lessonB = Lesson::factory()->for($course)->create(['title' => 'Beta Lesson']);

            $ascResponse = getJson(route('student.courses.lessons.index', [$course, 'sort' => 'title']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($lessonA->id, $ascIds))->toBeLessThan(array_search($lessonB->id, $ascIds));

            $descResponse = getJson(route('student.courses.lessons.index', [$course, 'sort' => '-title']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($lessonB->id, $descIds))->toBeLessThan(array_search($lessonA->id, $descIds));
        });

        it('sorts lessons by created_at (asc and desc)', function () {
            $student = User::factory()->create();
            Sanctum::actingAs($student);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $student->enrolledCourses()->attach($course);

            $oldLesson = Lesson::factory()->for($course)->create();
            DB::table('lessons')->where('id', $oldLesson->id)->update(['created_at' => now()->subDays(3)]);

            $newLesson = Lesson::factory()->for($course)->create();
            DB::table('lessons')->where('id', $newLesson->id)->update(['created_at' => now()->subDay()]);

            $ascResponse = getJson(route('student.courses.lessons.index', [$course, 'sort' => 'created_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldLesson->id, $ascIds))->toBeLessThan(array_search($newLesson->id, $ascIds));

            $descResponse = getJson(route('student.courses.lessons.index', [$course, 'sort' => '-created_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newLesson->id, $descIds))->toBeLessThan(array_search($oldLesson->id, $descIds));
        });

        it('fails if an invalid sort parameter is provided', function () {
            $student = User::factory()->create();
            Sanctum::actingAs($student);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $student->enrolledCourses()->attach($course);

            getJson(route('student.courses.lessons.index', [$course, 'sort' => 'invalid_field']))
                ->assertBadRequest();
        });

        it('returns empty data when no lessons match the search', function () {
            $student = User::factory()->create();
            Sanctum::actingAs($student);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $student->enrolledCourses()->attach($course);
            Lesson::factory()->for($course)->create();

            getJson(route('student.courses.lessons.index', [$course, 'filter[search]' => 'non-existent-lesson-title']))
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
            $student = User::factory()->create();
            Sanctum::actingAs($student);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $student->enrolledCourses()->attach($course);

            Lesson::factory()->count(3)->for($course)->create();

            getJson(route('student.courses.lessons.index', $course))
                ->assertOk()
                ->assertJsonStructure(paginationJsonStructure());
        });
    });
})->group('lesson', 'student');
