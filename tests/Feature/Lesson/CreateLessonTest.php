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
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('LessonController -> store', function () {
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
        it('fails if required fields are missing', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            postJson(route('course.lesson.store', $course), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title']);
        });

        it('fails if fields are invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            postJson(route('course.lesson.store', $course), creatingLessonPayload($course->type, [
                'title' => str_repeat('A', 256),
                'slug' => str_repeat('b', 256),
                'position' => -1
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'position']);
        });

        it('fails if slug is not unique within the same course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create(['slug' => 'existing-slug']);

            postJson(route('course.lesson.store', $course), creatingLessonPayload($course->type, [
                'slug' => $lesson->slug,
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if slug format is invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            postJson(route('course.lesson.store', $course), creatingLessonPayload($course->type, [
                'slug' => 'Invalid Slug!',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if OFFLINE fields are invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->type(CourseType::OFFLINE)->for($author, 'author')->create();

            postJson(route('course.lesson.store', $course), creatingLessonPayload($course->type, [
                'start_time' => now()->subDay(),
                'end_time' => now()->subDays(2),
                'address' => str_repeat('A', 256),
                'room_number' => str_repeat('1', 51),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['start_time', 'end_time', 'address', 'room_number']);
        });

        it('fails if ONLINE fields are invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->type(CourseType::ONLINE)->for($author, 'author')->create();

            postJson(route('course.lesson.store', $course), creatingLessonPayload($course->type, [
                'start_time' => now()->subDay(),
                'end_time' => now()->subDays(2),
                'meeting_link' => 'invalid-link',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['start_time', 'end_time', 'meeting_link']);
        });

        it('fails if VIDEO fields are invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->type(CourseType::VIDEO)->for($author, 'author')->create();

            postJson(route('course.lesson.store', $course), creatingLessonPayload($course->type, [
                'video_url' => 'invalid-url',
                'provider' => str_repeat('A', 51),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['video_url', 'provider']);
        });

        it('succeeds if a slug is provided manually', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $slug = 'test-slug';

            postJson(route('course.lesson.store', $course), creatingLessonPayload($course->type, [
                'slug' => $slug,
            ]))
                ->assertCreated()
                ->assertJsonFragment(['slug' => $slug])
                ->assertJsonStructure([
                    'data' => lessonJsonStructure($course->type),
                ]);
        });

        it('automatically assigns the next position to a new lesson', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create(['position' => 1]);

            postJson(route('course.lesson.store', $course), creatingLessonPayload($course->type))
                ->assertCreated()
                ->assertJsonFragment(['position' => $lesson->position + 1])
                ->assertJsonStructure([
                    'data' => lessonJsonStructure($course->type),
                ]);
            $this->assertDatabaseHas('lessons', [
                'course_id' => $course->id,
                'position' => $lesson->position + 1,
            ]);
        });

        it('succeeds if a position is provided manually', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $manualPosition = 99;

            postJson(route('course.lesson.store', $course), creatingLessonPayload($course->type, [
                'position' => $manualPosition
            ]))
                ->assertCreated()
                ->assertJsonFragment(['position' => $manualPosition])
                ->assertJsonStructure([
                    'data' => lessonJsonStructure($course->type),
                ]);
            $this->assertDatabaseHas('lessons', [
                'course_id' => $course->id,
                'position' => $manualPosition,
            ]);
        });

        it('returns not found for non-existing course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            postJson(route('course.lesson.store', 'non-existing-slug'), creatingLessonPayload($course->type))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if unauthenticated user tries to create a lesson', function () {
            $course = Course::factory()->create();

            postJson(route('course.lesson.store', $course), creatingLessonPayload($course->type))
                ->assertUnauthorized();
        });

        it('fails if users without permissions tries to create a lesson', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create();

            postJson(route('course.lesson.store', $course), creatingLessonPayload($course->type))
                ->assertForbidden();
        })->with([
            'student' => fn() => User::factory()->student()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('allows if user is the course author', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $data = creatingLessonPayload($course->type);

            postJson(route('course.lesson.store', $course), $data)
                ->assertCreated()
                ->assertJsonStructure([
                    'data' => lessonJsonStructure($course->type),
                ]);
            $this->assertDatabaseHas('lessons', [
                'title' => $data['title'],
                'course_id' => $course->id,
            ]);
        });

        it('allows users with permissions to create a lesson', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create();
            $data = creatingLessonPayload($course->type);

            postJson(route('course.lesson.store', $course), $data)
                ->assertCreated()
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
        it('flushes the lesson cache when a new lesson is created', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            Cache::tags([
                config('cache.tags.lesson_list'),
                config('cache.tags.course') . ':' . $course->id
            ])
                ->put('lessons', 'test_value', config('cache.ttl.lesson'));
            expect(Cache::tags([
                config('cache.tags.lesson_list'),
                config('cache.tags.course') . ':' . $course->id
            ])->get('lessons'))->not->toBeNull();

            postJson(route('course.lesson.store', $course), creatingLessonPayload($course->type))
                ->assertCreated()
                ->assertJsonStructure(['data' => lessonJsonStructure($course->type)]);
            expect(Cache::tags([
                config('cache.tags.lesson_list'),
                config('cache.tags.course') . ':' . $course->id
            ])->get('lessons'))->toBeNull();
        });
    });

})->group('lesson');
