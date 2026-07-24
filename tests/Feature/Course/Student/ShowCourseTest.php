<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('Student -> CourseController -> show', function () {
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
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            getJson(route('student.courses.show', 'non-existing-slug'))
                ->assertNotFound();
        });

        it('fails if student tries to retrieve a course they are not enrolled in', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            getJson(route('student.courses.show', $course))
                ->assertNotFound();
        });

        it('fails if student tries to retrieve an inactive course even if enrolled', function ($courseFactory) {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $course = $courseFactory($user);

            getJson(route('student.courses.show', $course))
                ->assertNotFound();
        })->with([
            'unpublished course' => fn() => fn($student) => Course::factory()
                ->unpublished()
                ->hasAttached($student, [], 'students')
                ->create(),
            'banned course' => fn() => fn($student) => Course::factory()
                ->banned()
                ->hasAttached($student, [], 'students')
                ->create(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to retrieve the course', function () {
            $course = Course::factory()->create();

            getJson(route('student.courses.show', $course))
                ->assertUnauthorized();
        });

        it('fails if a user without permissions tries to retrieve the course', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            getJson(route('student.courses.show', $course))
                ->assertForbidden();
        })->with([
            'unverified user' => fn() => User::factory()->unverified()->create(),
        ]);

        it('allows a student to retrieve their enrolled active course', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()
                ->hasAttached($user, [], 'students')
                ->create();

            getJson(route('student.courses.show', $course))
                ->assertOk()
                ->assertJsonStructure(['data' => studentCourseJsonStructure()]);
        })->with([
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ]);

        it('fails if a banned student tries to retrieve their enrolled course', function () {
            $bannedUser = User::factory()->banned()->create();
            $course = Course::factory()->hasAttached($bannedUser, [], 'students')->create();

            Sanctum::actingAs($bannedUser);

            getJson(route('student.courses.show', $course))
                ->assertForbidden();
        });
    });
})->group('course', 'student');
