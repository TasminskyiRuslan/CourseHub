<?php

use App\Models\User;
use App\Models\Course;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('CourseController -> destroy', function () {
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
        it('fails if the course does not exist', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            deleteJson(route('course.destroy', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to delete the course', function () {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();

            deleteJson(route('course.destroy', $course))
                ->assertUnauthorized();
            $this->assertDatabaseHas('courses', [
                'id' => $course->id,
            ]);
        });

        it('fails if users tries to delete someone else\'s course', function ($user) {
            $course = Course::factory()->create();

            if ($user) {
                Sanctum::actingAs($user);
            }

            deleteJson(route('course.destroy', $course))
                ->assertForbidden();
            $this->assertDatabaseHas('courses', [
                'id' => $course->id,
            ]);
        })->with([
            'student' => fn() => User::factory()->student()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows author to delete their own course', function () {
            Storage::fake('courses');
            $filename = 'test-image';

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->withImage($filename)->for($author, 'author')->create();
            Sanctum::actingAs($author);

            Storage::disk('courses')->put($filename, 'fake');

            deleteJson(route('course.destroy', $course))
                ->assertNoContent();
            $this->assertDatabaseMissing('courses', [
                'id' => $course->id,
            ]);
            Storage::disk('courses')->assertMissing($filename);
        });

        it('allows admins to delete the course and its image', function ($user) {
            Storage::fake('courses');
            $filename = 'test-image';

            $course = Course::factory()->withImage($filename)->create();

            if ($user) {
                Sanctum::actingAs($user);
            }

            Storage::disk('courses')->put($filename, 'fake');

            deleteJson(route('course.destroy', $course))
                ->assertNoContent();
            $this->assertDatabaseMissing('courses', [
                'id' => $course->id,
            ]);
            Storage::disk('courses')->assertMissing($filename);
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
    it('flushes the course cache when a course is updated', function () {
        $author = User::factory()->teacher()->create();
        $course = Course::factory()->for($author, 'author')->create();
        Sanctum::actingAs($author);

        Cache::tags([config('cache.tags.course')])->put('courses', 'test_value', config('cache.ttl.books'));

        deleteJson(route('course.destroy', $course))
            ->assertNoContent();

        expect(Cache::tags([config('cache.tags.course')])->get('courses'))->toBeNull();
    });
})->group('course');
