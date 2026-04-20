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
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
        Storage::fake('courses');
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
            expect($course->image_path)->not()->toBeNull();
            Storage::disk('courses')->assertExists($course->image_path);
        });

        it('fails if users tries to update someone else\'s course image', function ($user) {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->withImage()->create();
            Storage::disk('courses')->put($course->image_path, 'fake');

            if ($user) {
                Sanctum::actingAs($user);
            }

            deleteJson(route('course.image.destroy', $course))
                ->assertForbidden();

            $course->refresh();
            expect($course->image_path)->not()->toBeNull();
            Storage::disk('courses')->assertExists($course->image_path);
        })->with([
            'student' => fn() => User::factory()->student()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('allows author to delete their own course image', function () {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->withImage()->create();
            Sanctum::actingAs($author);

            deleteJson(route('course.image.destroy', $course))
                ->assertNoContent();
            $course->refresh();
            expect($course->image_path)->toBeNull();
            Storage::disk('courses')->assertMissing($course->image_path);
        });

        it('allows super-admin to update any course image', function () {
            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            $course = Course::factory()->create(['title' => 'Original Title']);
            Sanctum::actingAs($superAdmin);

            deleteJson(route('course.image.destroy', $course))
                ->assertNoContent();
            $course->refresh();
            expect($course->image_path)->toBeNull();
            Storage::disk('courses')->assertMissing($course->image_path);
        });
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

            deleteJson(route('course.image.destroy', 999))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when the course image is deleted', function () {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->withImage()->create();
            Sanctum::actingAs($author);

            Cache::tags([config('cache.tags.course')])->put('courses', 'test_value', config('cache.ttl.books'));

            deleteJson(route('course.image.update', $course))
                ->assertNoContent();
            expect(Cache::tags([config('cache.tags.course')])->get('courses'))->toBeNull();
        });
    });
})->group('course');
