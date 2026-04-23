<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('CourseImageController -> destroy', function () {
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
        it('fails if the course does not exist', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            deleteJson(route('course.image.destroy', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to delete the course image', function () {
            $course = Course::factory()->withImage()->create();
            Storage::disk('courses')->put($course->image_path, 'fake');

            deleteJson(route('course.image.destroy', $course))
                ->assertUnauthorized();
            $course->refresh();
            expect($course->image_path)->not->toBeNull();
            Storage::disk('courses')->assertExists($course->image_path);
        });

        it('fails if users tries to update someone else\'s course image', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->withImage()->create();
            Storage::disk('courses')->put($course->image_path, 'fake');

            deleteJson(route('course.image.destroy', $course))
                ->assertForbidden();
            $course->refresh();
            expect($course->image_path)->not->toBeNull();
            Storage::disk('courses')->assertExists($course->image_path);
        })->with([
            'student' => fn() => User::factory()->student()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('allows author to delete their own course image', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->withImage()->create();

            deleteJson(route('course.image.destroy', $course))
                ->assertNoContent();
            $course->refresh();
            expect($course->image_path)->toBeNull();
            Storage::disk('courses')->assertMissing($course->image_path);
        });

        it('allows users with permissions to update any course image', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create();

            deleteJson(route('course.image.destroy', $course))
                ->assertNoContent();
            $course->refresh();
            expect($course->image_path)->toBeNull();
            Storage::disk('courses')->assertMissing($course->image_path);
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
        it('flushes the cache when the course image is deleted', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->withImage()->create();
            Cache::tags([config('cache.tags.course_list')])->put('courses', 'test_value', config('cache.ttl.course'));
            expect(Cache::tags([config('cache.tags.course_list')])->get('courses'))->not->toBeNull();

            deleteJson(route('course.image.update', $course))
                ->assertNoContent();
            expect(Cache::tags([config('cache.tags.course_list')])->get('courses'))->toBeNull();
        });
    });
})->group('course');
