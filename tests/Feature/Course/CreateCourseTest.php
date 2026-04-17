<?php

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('CourseController -> store', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if unauthenticated user tries to create a course', function () {
            postJson(route('course.store'), creatingCoursePayload())
                ->assertUnauthorized();
        });

        it('fails if users without course:create tries to create a course', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }
            postJson(route('course.store'), creatingCoursePayload())
                ->assertForbidden();
        })->with([
            'unverified student' => fn() => User::factory()->student()->unverified()->create(),
            'verified student' => fn() => User::factory()->student()->verified()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('allows users with course:create to create a course', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }
            $data = creatingCoursePayload();

            postJson(route('course.store'), $data)
                ->assertCreated()
                ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true, withLessons: true, courseType: $data['type'])]);

            $this->assertDatabaseHas('courses', [
                'title' => $data['title'],
                'author_id' => $user->id,
            ]);
        })->with([
            'teacher' => fn() => User::factory()->teacher()->verified()->create(),
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if required fields are missing', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            postJson(route('course.store'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'type', 'price']);
        });

        it('fails if fields are too long', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            postJson(route('course.store'), creatingCoursePayload([
                'title' => str_repeat('A', 256),
                'slug' => str_repeat('B', 256),
                'description' => str_repeat('C', 5001),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'description']);
        });

        it('fails if type is invalid', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            postJson(route('course.store'), creatingCoursePayload(['type' => 'invalid-type']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['type']);
        });

        it('fails if price is not numeric', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            postJson(route('course.store'), creatingCoursePayload(['price' => 'invalid-price']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['price']);
        });

        it('fails if price is out of range', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            postJson(route('course.store'), creatingCoursePayload(['price' => '-10']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['price']);
        });

        it('fails if slug is not unique', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);
            Course::factory()->create(['slug' => 'existing-slug']);

            postJson(route('course.store'), creatingCoursePayload(['slug' => 'existing-slug']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if slug format is invalid', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            postJson(route('course.store'), creatingCoursePayload(['slug' => 'Invalid Slug!']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('succeeds if a slug is provided manually', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);
            $slug = 'test-slug';

            postJson(route('course.store'), creatingCoursePayload(['slug' => $slug]))
                ->assertCreated()
                ->assertJsonFragment(['slug' => $slug]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the course cache when a new course is created', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            Cache::tags([config('cache.tags.course')])->put('courses', 'test_value', config('cache.ttl.books'));
            expect(Cache::tags([config('cache.tags.course')])->get('courses'))->not->toBeNull();

            postJson(route('course.store'), creatingCoursePayload())
                ->assertCreated();

            expect(Cache::tags([config('cache.tags.course')])->get('courses'))->toBeNull();
        });
    });
})->group('course');
