<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\patchJson;
use function PHPUnit\Framework\assertFalse;

uses(RefreshDatabase::class);

describe('PublishCourseController', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
        Storage::fake('courses');
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

            patchJson(route('course.publish', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to publish the course', function () {
            $course = Course::factory()->unpublished()->create();

            patchJson(route('course.publish', $course))
                ->assertUnauthorized();
            $course->refresh();
            expect($course->is_published)->toBeFalse();
        });

        it('fails if users tries to publish someone else\'s course', function ($user) {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->unpublished()->create();

            if ($user) {
                Sanctum::actingAs($user);
            }

            patchJson(route('course.publish', $course))
                ->assertForbidden();
            $course->refresh();
            expect($course->is_published)->toBeFalse();
        })->with([
            'student' => fn() => User::factory()->student()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('allows author to publish their own course', function () {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->unpublished()->create();
            Sanctum::actingAs($author);

            patchJson(route('course.publish', $course))
                ->assertOk()
                ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true, withLessons: true, courseType: $course->type)]);
            $course->refresh();
            expect($course->is_published)->toBeTrue();
        });

        it('allows super-admin to publish any course', function () {
            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            $course = Course::factory()->unpublished()->create();
            Sanctum::actingAs($superAdmin);

            patchJson(route('course.publish', $course))
                ->assertOk()
                ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true, withLessons: true, courseType: $course->type)]);
            $course->refresh();
            expect($course->is_published)->toBeTrue();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when the course is published', function () {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->unpublished()->create();
            Sanctum::actingAs($author);

            Cache::tags([config('cache.tags.course')])->put('courses', 'test_value', config('cache.ttl.books'));

            patchJson(route('course.publish', $course))
                ->assertOK();
            expect(Cache::tags([config('cache.tags.course')])->get('courses'))->toBeNull();
        });
    });
})->group('course');
