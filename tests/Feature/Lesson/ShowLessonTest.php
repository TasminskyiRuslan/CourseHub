<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('LessonController -> show', function () {
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
        it('fails if the course does not exist', function () {
            $lesson = Lesson::factory()->create();

            getJson(route('course.lesson.show', ['non-existing-slug', $lesson]))
                ->assertNotFound();
        });

        it('fails if the lesson does not exist', function () {
            $course = Course::factory()->create();

            getJson(route('course.lesson.show', [$course, 'non-existing-slug']))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('allows users to retrieve the lesson', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('course.lesson.show', [$course, $lesson]))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => lessonJsonStructure($course->type)
                ]);
        })->with([
            'guest' => null,
            'unverified' => fn() => User::factory()->unverified()->create(),
            'student' => fn() => User::factory()->student()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);

        it('allows an author to retrieve own lesson from unpublished course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->unpublished()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('course.lesson.show', [$course, $lesson]))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => lessonJsonStructure($course->type)
                ]);
        });

        it('allows users with permissions to retrieve lesson from unpublished course', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->unpublished()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('course.lesson.show', [$course, $lesson]))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => lessonJsonStructure($course->type)
                ]);
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);

        it('fails if users without permissions tries to retrieve lesson from unpublished course', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->unpublished()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('course.lesson.show', [$course, $lesson]))
                ->assertForbidden();
        })->with([
            'guest' => null,
            'student' => fn() => User::factory()->student()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);
    });
})->group('lesson');
