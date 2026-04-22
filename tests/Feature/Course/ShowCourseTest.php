<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('CourseController -> show', function () {
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
        it('returns not found for non-existing course', function () {
            getJson(route('course.show', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('allows users to retrieve the course', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create();

            getJson(route('course.show', $course))
                ->assertOk()
                ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true)]);
        })->with([
            'guest' => null,
            'unverified' => fn() => User::factory()->unverified()->create(),
            'student' => fn() => User::factory()->student()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);

        it('allows an author to retrieve own unpublished course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $unpublishedCourse = Course::factory()->unpublished()->for($author, 'author')->create();

            getJson(route('course.show', $unpublishedCourse))
                ->assertOk()
                ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true)]);
        });

        it('allows admins to retrieve any unpublished course', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $unpublishedCourse = Course::factory()->unpublished()->create();

            getJson(route('course.show', $unpublishedCourse))
                ->assertOk()
                ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true)]);
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);

        it('fails if users without permissions tries to retrieve unpublished course', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $unpublishedCourse = Course::factory()->unpublished()->create();

            getJson(route('course.show', $unpublishedCourse))
                ->assertForbidden();
        })->with([
            'guest' => null,
            'student' => fn() => User::factory()->student()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);
    });
})->group('course');
