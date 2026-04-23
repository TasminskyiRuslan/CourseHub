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

describe('LessonController -> update', function () {
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

            patchJson(route('course.lesson.update', [$course, $lesson]), updatingLessonPayload($course->type, [
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

            patchJson(route('course.lesson.update', [$course, $lesson]), updatingLessonPayload($course->type, [
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

            patchJson(route('course.lesson.update', [$course, $lesson]), updatingLessonPayload($course->type, [
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

            patchJson(route('course.lesson.update', [$course, $lesson]), updatingLessonPayload($course->type, [
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

            patchJson(route('course.lesson.update', [$course, $lesson]), updatingLessonPayload($course->type, [
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

            patchJson(route('course.lesson.update', [$course, $lesson]), updatingLessonPayload($course->type, [
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

            patchJson(route('course.lesson.update', [$course, $lesson]), updatingLessonPayload($course->type, [
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

            patchJson(route('course.lesson.update', [$course, $lesson]), updatingLessonPayload($course->type, [
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

            patchJson(route('course.lesson.update', [$course, $lesson]), updatingLessonPayload($course->type, [
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

            patchJson(route('course.lesson.update', ['non-existing-slug', $lesson]))
                ->assertNotFound();
        });

        it('fails if the lesson does not exist', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            patchJson(route('course.lesson.update', [$course, 'non-existing-slug']))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if unauthenticated user tries to update a lesson', function () {
            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            patchJson(route('course.lesson.update', [$course, $lesson]), updatingLessonPayload($course->type, ['title' => 'Updated Title']))
                ->assertUnauthorized();
        });

        it('fails if users without permissions tries to update a lesson', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            patchJson(route('course.lesson.update', [$course, $lesson]), updatingLessonPayload($course->type, ['title' => 'Updated Title']))
                ->assertForbidden();
        })->with([
            'student' => fn() => User::factory()->student()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('allows if user is the course author to update a lesson', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();
            $data = updatingLessonPayload($course->type, ['title' => 'Updated Title']);

            patchJson(route('course.lesson.update', [$course, $lesson]), $data)
                ->assertOk()
                ->assertJsonPath('data.title', $data['title'])
                ->assertJsonStructure(['data' => lessonJsonStructure($course->type)]);
            $this->assertDatabaseHas('lessons', [
                'id' => $lesson->id,
                'title' => $data['title'],
            ]);
        });

        it('allows users with permissions to update a lesson', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();
            $data = updatingLessonPayload($course->type, ['title' => 'Updated Title']);

            patchJson(route('course.lesson.update', [$course, $lesson]), $data)
                ->assertOk()
                ->assertJsonPath('data.title', $data['title'])
                ->assertJsonStructure([
                    'data' => lessonJsonStructure($course->type),
                ]);
            $this->assertDatabaseHas('lessons', [
                'title' => $data['title'],
                'course_id' => $course->id,
            ]);
        })->with([
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);
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

            Cache::tags([
                config('cache.tags.lesson_list'),
                config('cache.tags.course') . ':' . $course->id
            ])
                ->put('lessons', 'test_value', config('cache.ttl.lesson'));
            expect(Cache::tags([
                config('cache.tags.lesson_list'),
                config('cache.tags.course') . ':' . $course->id
            ])->get('lessons'))->not->toBeNull();

            patchJson(route('course.lesson.update', [$course, $lesson]), updatingLessonPayload($course->type, ['title' => 'New Title']))
                ->assertOk()
                ->assertJsonStructure(['data' => lessonJsonStructure($course->type)]);
            expect(Cache::tags([
                config('cache.tags.lesson_list'),
                config('cache.tags.course') . ':' . $course->id
            ])->get('lessons'))->toBeNull();
        });
    });
})->group('lesson');
