<?php

declare(strict_types=1);

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('Teacher -> LessonController -> store', function () {
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

            postJson(route('teacher.courses.lessons.store', $course), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title']);
        });

        it('fails if fields are invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            postJson(route('teacher.courses.lessons.store', $course), creatingLessonPayload($course->type, [
                'title' => str_repeat('A', 256),
                'slug' => str_repeat('b', 256),
                'position' => -1,
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'position']);
        });

        it('fails if slug is not unique within the same course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create(['slug' => 'existing-slug']);

            postJson(route('teacher.courses.lessons.store', $course), creatingLessonPayload($course->type, [
                'slug' => $lesson->slug,
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if slug format is invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            postJson(route('teacher.courses.lessons.store', $course), creatingLessonPayload($course->type, [
                'slug' => 'Invalid Slug!',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if OFFLINE fields are invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->type(CourseType::OFFLINE)->for($author, 'author')->create();

            postJson(route('teacher.courses.lessons.store', $course), creatingLessonPayload($course->type, [
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

            postJson(route('teacher.courses.lessons.store', $course), creatingLessonPayload($course->type, [
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

            postJson(route('teacher.courses.lessons.store', $course), creatingLessonPayload($course->type, [
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

            postJson(route('teacher.courses.lessons.store', $course), creatingLessonPayload($course->type, [
                'slug' => $slug,
            ]))
                ->assertCreated()
                ->assertJsonFragment(['slug' => $slug])
                ->assertJsonStructure([
                    'data' => teacherLessonJsonStructure($course->type),
                ]);
        });

        it('automatically assigns the next position to a new lesson', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create(['position' => 1]);

            postJson(route('teacher.courses.lessons.store', $course), creatingLessonPayload($course->type))
                ->assertCreated()
                ->assertJsonFragment(['position' => $lesson->position + 1])
                ->assertJsonStructure([
                    'data' => teacherLessonJsonStructure($course->type),
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

            postJson(route('teacher.courses.lessons.store', $course), creatingLessonPayload($course->type, [
                'position' => $manualPosition,
            ]))
                ->assertCreated()
                ->assertJsonFragment(['position' => $manualPosition])
                ->assertJsonStructure([
                    'data' => teacherLessonJsonStructure($course->type),
                ]);
            $this->assertDatabaseHas('lessons', [
                'course_id' => $course->id,
                'position' => $manualPosition,
            ]);
        });

        it('fails if the course does not exist', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            postJson(route('teacher.courses.lessons.store', 'non-existing-slug'), creatingLessonPayload($course->type))
                ->assertNotFound();
        });

        it('fails if a non-author user tries to create a lesson', function (?User $user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            postJson(route('teacher.courses.lessons.store', $course), creatingLessonPayload($course->type))
                ->assertNotFound();
        })->with([
            'user' => fn () => User::factory()->create(),
            'unverified teacher' => fn () => User::factory()->teacher()->unverified()->create(),
            'admin' => fn () => User::factory()->admin()->create(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to create a lesson', function () {
            $course = Course::factory()->create();

            postJson(route('teacher.courses.lessons.store', $course), creatingLessonPayload($course->type))
                ->assertUnauthorized();
        });

        it('allows a user with permission to create a lesson', function (?User $user, Factory $courseFactory) {
            Sanctum::actingAs($user);

            $course = $courseFactory->for($user, 'author')->create();

            $data = creatingLessonPayload($course->type);

            postJson(route('teacher.courses.lessons.store', $course), $data)
                ->assertCreated()
                ->assertJsonStructure(['data' => teacherLessonJsonStructure($course->type)]);

            $this->assertDatabaseHas('lessons', [
                'title' => $data['title'],
                'course_id' => $course->id,
            ]);
        })->with([
            'teacher' => fn () => User::factory()->teacher()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published course' => fn () => Course::factory(),
            'unpublished course' => fn () => Course::factory()->unpublished(),
            'banned course' => fn () => Course::factory()->banned(),
        ]);

        it('fails if a banned user tries to create a lesson', function () {
            $bannedAuthor = User::factory()->teacher()->banned()->create();
            $course = Course::factory()->for($bannedAuthor, 'author')->create();

            Sanctum::actingAs($bannedAuthor);

            postJson(route('teacher.courses.lessons.store', $course), creatingLessonPayload($course->type))
                ->assertForbidden();
        });
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

            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.lesson'));
            expect(Cache::tags($tags)->get($cacheKey))->not->toBeNull();

            postJson(route('teacher.courses.lessons.store', $course), creatingLessonPayload($course->type))
                ->assertCreated();

            expect(Cache::tags($tags)->get($cacheKey))->toBeNull();
        });
    });
})->group('lesson', 'teacher');
