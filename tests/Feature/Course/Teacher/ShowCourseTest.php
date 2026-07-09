<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('Teacher -> CourseController -> show', function () {
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
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            getJson(route('teacher.course.show', 'non-existing-slug'))
                ->assertNotFound();
        });

        it('fails if non-author users try to retrieve the course', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            getJson(route('teacher.course.show', $course))
                ->assertNotFound();
        })->with([
            'another teacher' => fn() => User::factory()->teacher()->create(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if unauthenticated users tries to retrieve the course', function () {
            $course = Course::factory()->create();

            getJson(route('teacher.course.show', $course))
                ->assertUnauthorized();
        });

        it('fails if users without permissions try to retrieve the course', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            getJson(route('teacher.course.show', $course))
                ->assertForbidden();
        })->with([
            'user' => fn() => User::factory()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('allows users to retrieve their own course', function ($userClosure, $courseClosure) {
            $user = $userClosure();

            Sanctum::actingAs($user);

            $courseInnerClosure = $courseClosure();
            $course = $courseInnerClosure($user);

            getJson(route('teacher.course.show', $course))
                ->assertOk()
                ->assertJsonStructure(['data' => teacherCourseJsonStructure()]);
        })->with([
            'teacher' => fn() => User::factory()->teacher()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published' => fn() => fn($author) => Course::factory()->for($author, 'author')->create(),
            'unpublished' => fn() => fn($author) => Course::factory()->unpublished()->for($author, 'author')->create(),
            'banned' => fn() => fn($author) => Course::factory()->banned()->for($author, 'author')->create(),
        ]);
    });
})->group('course', 'teacher');
