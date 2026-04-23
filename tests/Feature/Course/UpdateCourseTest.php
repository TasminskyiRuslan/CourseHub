<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('CourseController -> update', function () {
    beforeEach(function () {
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

            patchJson(route('course.update', $course), updatingCoursePayload([
                'title' => '',
                'slug' => '',
                'price' => ''
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'price']);
        });

        it('fails if the present fields are null', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            patchJson(route('course.update', $course), updatingCoursePayload([
                'title' => null,
                'slug' => null,
                'price' => null
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'price']);
        });

        it('fails if the fields are too long', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            patchJson(route('course.update', $course), updatingCoursePayload([
                'title' => str_repeat('A', 256),
                'slug' => str_repeat('B', 256),
                'description' => str_repeat('C', 5001),
                'price' => 999999999.99,
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'description', 'price']);
        });

        it('fails if the slug is taken by another course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create(['slug' => 'my-slug']);
            $anotherCourse = Course::factory()->for($author, 'author')->create(['slug' => 'taken-slug']);

            patchJson(route('course.update', $course), updatingCoursePayload([
                'slug' => $anotherCourse->slug,
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if the slug format is invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            patchJson(route('course.update', $course), updatingCoursePayload([
                'slug' => 'Invalid Slug!'
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('succeeds if the slug remains the same (ignore current)', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            patchJson(route('course.update', $course), [
                'slug' => $course->slug,
            ])
                ->assertOk()
                ->assertJsonFragment(['slug' => $course->slug])
                ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true)]);
        });

        it('fails if price is invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            patchJson(route('course.update', $course), ['price' => 'not-a-number'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['price']);
        });

        it('fails if the course does not exist', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            patchJson(route('course.update', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if unauthenticated user tries to update a course', function () {
            $course = Course::factory()->create();

            patchJson(route('course.update', $course), updatingCoursePayload())
                ->assertUnauthorized();
        });

        it('fails if users without permissions tries to update someone else\'s course', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create();

            patchJson(route('course.update', $course), updatingCoursePayload())
                ->assertForbidden();
        })->with([
            'student' => fn() => User::factory()->student()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('allows author to update their own course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $data = updatingCoursePayload();

            patchJson(route('course.update', $course), $data)
                ->assertOk()
                ->assertJsonPath('data.title', $data['title'])
                ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true)]);
            $this->assertDatabaseHas('courses', [
                'id' => $course->id,
                'title' => $data['title'],
                'description' => $data['description'],
            ]);
        });

        it('allows users with permissions to update any course', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create(['title' => 'Original Title']);
            $data = updatingCoursePayload();

            patchJson(route('course.update', $course), $data)
                ->assertOk()
                ->assertJsonPath('data.title', $data['title'])
                ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true)]);
            $this->assertDatabaseHas('courses', [
                'id' => $course->id,
                'title' => $data['title'],
                'description' => $data['description'],
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
    it('flushes the course cache when a course is updated', function () {
        $author = User::factory()->teacher()->create();
        Sanctum::actingAs($author);

        $course = Course::factory()->for($author, 'author')->create();
        Cache::tags([config('cache.tags.course_list')])->put('courses', 'test_value', config('cache.ttl.course'));
        expect(Cache::tags([config('cache.tags.course_list')])->get('courses'))->not->toBeNull();

        patchJson(route('course.update', $course), updatingCoursePayload())
            ->assertOk()
            ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true)]);
        expect(Cache::tags([config('cache.tags.course_list')])->get('courses'))->toBeNull();
    });
})->group('course');
