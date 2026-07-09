<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('Teacher -> CourseController -> store', function () {
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
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            postJson(route('teacher.course.store'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'type', 'price']);
        });

        it('fails if fields are invalid', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            postJson(route('teacher.course.store'), creatingCoursePayload([
                'title' => str_repeat('A', 256),
                'slug' => str_repeat('B', 256),
                'description' => str_repeat('C', 5001),
                'type' => 'invalid-type',
                'price' => 'invalid-price'
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'description', 'type', 'price']);
        });

        it('fails if price is out of range', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            postJson(route('teacher.course.store'), creatingCoursePayload(['price' => '-10']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['price']);
        });

        it('fails if slug is not unique', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->create(['slug' => 'existing-slug']);

            postJson(route('teacher.course.store'), creatingCoursePayload(['slug' => $course->slug]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if slug format is invalid', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            postJson(route('teacher.course.store'), creatingCoursePayload(['slug' => 'Invalid Slug!']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('succeeds if a slug is provided manually', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $slug = 'test-slug';

            postJson(route('teacher.course.store'), creatingCoursePayload(['slug' => $slug]))
                ->assertCreated()
                ->assertJsonFragment(['slug' => $slug])
                ->assertJsonStructure(['data' =>  teacherCourseJsonStructure()]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if unauthenticated user tries to create a course', function () {
            postJson(route('teacher.course.store'), creatingCoursePayload())
                ->assertUnauthorized();
        });

        it('fails if users without permissions tries to create a course', function ($user) {
            Sanctum::actingAs($user);

            postJson(route('teacher.course.store'), creatingCoursePayload())
                ->assertForbidden();
        })->with([
            'user' => fn() => User::factory()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('allows users with permission to create a course', function ($user) {
            Sanctum::actingAs($user);

            $data = creatingCoursePayload();

            postJson(route('teacher.course.store'), $data)
                ->assertCreated()
                ->assertJsonStructure(['data' => teacherCourseJsonStructure()]);
            $this->assertDatabaseHas('courses', [
                'title' => $data['title'],
                'author_id' => $user->id,
            ]);
        })->with([
            'teacher' => fn() => User::factory()->teacher()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the course cache when a new course is created', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $cacheKey = "courses:page:1";
            Cache::tags([config('cache.tags.course_list')])->put($cacheKey, 'test_value', config('cache.ttl.course'));
            expect(Cache::tags([config('cache.tags.course_list')])->get($cacheKey))->not->toBeNull();

            postJson(route('teacher.course.store'), creatingCoursePayload())
                ->assertCreated();
            expect(Cache::tags([config('cache.tags.course_list')])->get($cacheKey))->toBeNull();
        });
    });
})->group('course', 'teacher');
