<?php

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('Teacher -> LessonController -> update', function () {
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
        it('fails if the present fields are empty', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            patchJson(route('teacher.courses.lessons.update', [$course, $lesson]), updatingLessonPayload($course->type, [
                'title' => '',
                'slug' => '',
                'position' => ''
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'position']);
        });

        it('fails if the present fields are null', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            patchJson(route('teacher.courses.lessons.update', [$course, $lesson]), updatingLessonPayload($course->type, [
                'title' => null,
                'slug' => null,
                'position' => null
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'position']);
        });

        it('fails if fields are invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            patchJson(route('teacher.courses.lessons.update', [$course, $lesson]), updatingLessonPayload($course->type, [
                'title' => str_repeat('A', 256),
                'slug' => str_repeat('b', 256),
                'position' => -1
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'position']);
        });

        it('fails if slug is taken by another lesson within the same course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();
            $anotherLesson = Lesson::factory()->for($course, 'course')->create(['slug' => 'taken-slug']);

            patchJson(route('teacher.courses.lessons.update', [$course, $lesson]), updatingLessonPayload($course->type, [
                'slug' => $anotherLesson->slug,
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('succeeds if the slug remains the same (ignore current)', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            patchJson(route('teacher.courses.lessons.update', [$course, $lesson]), updatingLessonPayload($course->type, [
                'slug' => $lesson->slug,
            ]))
                ->assertOk()
                ->assertJsonFragment(['slug' => $lesson->slug]);
        });

        it('fails if slug format is invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            patchJson(route('teacher.courses.lessons.update', [$course, $lesson]), updatingLessonPayload($course->type, [
                'slug' => 'Invalid Slug!',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if OFFLINE fields are invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->type(CourseType::OFFLINE)->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            patchJson(route('teacher.courses.lessons.update', [$course, $lesson]), updatingLessonPayload($course->type, [
                'start_time' => now(),
                'end_time' => now()->subDays(2),
                'address' => str_repeat('A', 256),
                'room_number' => str_repeat('1', 51),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['end_time', 'address', 'room_number']);
        });

        it('fails if ONLINE fields are invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->type(CourseType::ONLINE)->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            patchJson(route('teacher.courses.lessons.update', [$course, $lesson]), updatingLessonPayload($course->type, [
                'start_time' => now(),
                'end_time' => now()->subDays(2),
                'meeting_link' => 'not-a-url',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['end_time', 'meeting_link']);
        });

        it('fails if VIDEO fields are invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->type(CourseType::VIDEO)->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            patchJson(route('teacher.courses.lessons.update', [$course, $lesson]), updatingLessonPayload($course->type, [
                'video_url' => 'invalid-url',
                'provider' => str_repeat('A', 51),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['video_url', 'provider']);
        });

        it('fails if the course does not exist', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            patchJson(route('teacher.courses.lessons.update', ['non-existing-slug', $lesson]), updatingLessonPayload($course->type))
                ->assertNotFound();
        });

        it('fails if the lesson does not exist', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            patchJson(route('teacher.courses.lessons.update', [$course, 'non-existing-slug']), updatingLessonPayload($course->type))
                ->assertNotFound();
        });

        it('fails if the lesson is from another course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->create();

            patchJson(route('teacher.courses.lessons.update', [$course, $lesson]), updatingLessonPayload($course->type))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to update a lesson', function () {
            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            patchJson(route('teacher.courses.lessons.update', [$course, $lesson]), updatingLessonPayload($course->type, ['title' => 'Updated Title']))
                ->assertUnauthorized();
        });

        it('fails if a user without permissions tries to update a lesson', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            patchJson(route('teacher.courses.lessons.update', [$course, $lesson]), updatingLessonPayload($course->type, ['title' => 'Updated Title']))
                ->assertForbidden();
        })->with([
            'user' => fn() => User::factory()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('allows a user with permission to update a lesson', function ($userClosure, $courseClosure) {
            $user = $userClosure();
            Sanctum::actingAs($user);

            $courseInnerClosure = $courseClosure();
            $course = $courseInnerClosure($user);
            $lesson = Lesson::factory()->for($course, 'course')->create();

            $data = updatingLessonPayload($course->type, ['title' => 'Updated Title']);

            patchJson(route('teacher.courses.lessons.update', [$course, $lesson]), $data)
                ->assertOk()
                ->assertJsonPath('data.title', $data['title'])
                ->assertJsonStructure(['data' => teacherLessonJsonStructure($course->type)]);

            $this->assertDatabaseHas('lessons', [
                'id' => $lesson->id,
                'title' => $data['title'],
            ]);
        })->with([
            'teacher' => fn() => User::factory()->teacher()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published' => fn() => fn($author) => Course::factory()->for($author, 'author')->create(),
            'unpublished' => fn() => fn($author) => Course::factory()->unpublished()->for($author, 'author')->create(),
            'banned' => fn() => fn($author) => Course::factory()->banned()->for($author, 'author')->create(),
        ]);

        it('fails if a banned user tries to update a lesson', function () {
            $bannedAuthor = User::factory()->teacher()->banned()->create();
            $course = Course::factory()->for($bannedAuthor, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            Sanctum::actingAs($bannedAuthor);

            patchJson(route('teacher.courses.lessons.update', [$course, $lesson]), updatingLessonPayload($course->type))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when a lesson is updated', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.lesson'));
            expect(Cache::tags($tags)->get($cacheKey))->not->toBeNull();

            patchJson(route('teacher.courses.lessons.update', [$course, $lesson]), updatingLessonPayload($course->type, ['title' => 'New Title']))
                ->assertOk()
                ->assertJsonStructure(['data' => teacherLessonJsonStructure($course->type)]);

            expect(Cache::tags($tags)->get($cacheKey))->toBeNull();
        });
    });
})->group('lesson', 'teacher');
