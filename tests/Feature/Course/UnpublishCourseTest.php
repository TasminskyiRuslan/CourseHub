<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('UnpublishCourseController', function () {
    beforeEach(function () {
        Cache::flush();
        Storage::fake('courses');
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('returns not found for non-existing course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            patchJson(route('course.unpublish', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to unpublish the course', function () {
            $course = Course::factory()->create();

            patchJson(route('course.unpublish', $course))
                ->assertUnauthorized();
            $course->refresh();
            expect($course->is_published)->toBeTrue();
        });

        it('fails if users tries to unpublish someone else\'s course', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create();

            patchJson(route('course.unpublish', $course))
                ->assertForbidden();
            $course->refresh();
            expect($course->is_published)->toBeTrue();
        })->with([
            'student' => fn() => User::factory()->student()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows author to unpublish their own course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            patchJson(route('course.unpublish', $course))
                ->assertOk()
                ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true)]);
            $course->refresh();
            expect($course->is_published)->toBeFalse();
        });

        it('allows admins to unpublish any course', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create();

            patchJson(route('course.unpublish', $course))
                ->assertOk()
                ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true)]);
            $course->refresh();
            expect($course->is_published)->toBeFalse();
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when the course is unpublished', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            Cache::tags([config('cache.tags.course_list')])->put('courses', 'test_value', config('cache.ttl.books'));

            patchJson(route('course.unpublish', $course))
                ->assertOK()
                ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true)]);
            expect(Cache::tags([config('cache.tags.course_list')])->get('courses'))->toBeNull();
        });
    });
})->group('course');
