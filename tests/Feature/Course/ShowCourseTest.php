<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CourseJsonStructure;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('CourseController -> show', function () {
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
            $course = Course::factory()->create();
            if ($user) {
                Sanctum::actingAs($user);
            }
            getJson(route('course.show', $course))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => courseJsonStructure(withAuthor: true, withLessonsCount: true, withLessons: true, courseType: $course->type),
                ]);
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
            $unpublishedCourse = Course::factory()->unpublished()->for($author, 'author')->create();
            Sanctum::actingAs($author);

            getJson(route('course.show', $unpublishedCourse))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => courseJsonStructure(withAuthor: true, withLessonsCount: true, withLessons: true, courseType: $unpublishedCourse->type),
                ]);
        });

        it('allows admins to retrieve any unpublished course', function ($user) {
            $unpublishedCourse = Course::factory()->unpublished()->create();
            if ($user) {
                Sanctum::actingAs($user);
            }

            getJson(route('course.show', $unpublishedCourse))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => courseJsonStructure(withAuthor: true, withLessonsCount: true, withLessons: true, courseType: $unpublishedCourse->type),
                ]);
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);

        it('fails if user without permissions tries to retrieve unpublished course', function () {
            $user = User::factory()->teacher()->create();
            $unpublishedCourse = Course::factory()->unpublished()->for($user, 'author')->create();
            $user = User::factory()->teacher()->create();
            Sanctum::actingAs($user);

            getJson(route('course.show', $unpublishedCourse))
                ->assertForbidden();
        });
    });
})->group('course');
