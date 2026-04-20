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
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if unauthenticated user tries to update a course', function () {
            $course = Course::factory()->create();

            patchJson(route('course.update', $course), updatingCoursePayload())
                ->assertUnauthorized();
        });

        it('fails if users tries to update someone else\'s course', function ($user) {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();

            if ($user) {
                Sanctum::actingAs($user);
            }

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
            $course = Course::factory()->for($author, 'author')->create();
            Sanctum::actingAs($author);

            $data = updatingCoursePayload();

            patchJson(route('course.update', $course), $data)
                ->assertOk()
                ->assertJsonPath('data.title', $data['title']);
            $this->assertDatabaseHas('courses', [
                'id' => $course->id,
                'title' => $data['title'],
                'description' => $data['description'],
            ]);
        });

        it('allows super-admin to update any course', function () {
            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            $course = Course::factory()->create(['title' => 'Original Title']);
            Sanctum::actingAs($superAdmin);

            $data = updatingCoursePayload();

            patchJson(route('course.update', $course), $data)
                ->assertOk()
                ->assertJsonPath('data.title', $data['title']);
            $this->assertDatabaseHas('courses', [
                'id' => $course->id,
                'title' => $data['title'],
                'description' => $data['description'],
            ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the present fields are empty', function () {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();

            Sanctum::actingAs($author);

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
            $course = Course::factory()->for($author, 'author')->create();

            Sanctum::actingAs($author);

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
            $course = Course::factory()->for($author, 'author')->create();

            Sanctum::actingAs($author);

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
            $course = Course::factory()->for($author, 'author')->create(['slug' => 'my-slug']);
            $anotherCourse = Course::factory()->for($author, 'author')->create(['slug' => 'taken-slug']);
            Sanctum::actingAs($author);

            patchJson(route('course.update', $course), updatingCoursePayload([
                'slug' => $anotherCourse->slug,
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if the slug format is invalid', function () {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            Sanctum::actingAs($author);

            patchJson(route('course.update', $course), updatingCoursePayload([
                'slug' => 'Invalid Slug!'
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('succeeds if the slug remains the same (ignore current)', function () {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            Sanctum::actingAs($author);

            patchJson(route('course.update', $course), [
                'slug' => $course->slug,
            ])
                ->assertOk()
                ->assertJsonFragment(['slug' => $course->slug]);
        });

        it('fails if the book does not exist', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            patchJson(route('course.update', 999))
                ->assertNotFound();
        });

        it('fails if price is invalid', function () {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();

            Sanctum::actingAs($author);

            patchJson(route('course.update', $course), ['price' => 'not-a-number'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['price']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    it('flushes the course cache when a course is updated', function () {
        $author = User::factory()->teacher()->create();
        $course = Course::factory()->for($author, 'author')->create();
        Sanctum::actingAs($author);

        Cache::tags([config('cache.tags.course')])->put('courses', 'test_value', config('cache.ttl.course'));

        patchJson(route('course.update', $course), updatingCoursePayload())
            ->assertOk();
        expect(Cache::tags([config('cache.tags.course')])->get('courses'))->toBeNull();
    });
})->group('course');
