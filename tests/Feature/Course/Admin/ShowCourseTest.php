<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('Admin -> CourseController -> show', function () {
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
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            getJson(route('admin.courses.show', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to retrieve a course', function () {
            $course = Course::factory()->create();

            getJson(route('admin.courses.show', $course))
                ->assertUnauthorized();
        });

        it('fails if a user without permissions tries to retrieve a course', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            getJson(route('admin.courses.show', $course))
                ->assertForbidden();
        })->with([
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows a user with permissions to retrieve a course', function ($userClosure, $courseClosure) {
            $user = $userClosure();
            Sanctum::actingAs($user);

            $course = $courseClosure();

            getJson(route('admin.courses.show', $course))
                ->assertOk()
                ->assertJsonStructure(['data' => adminCourseJsonStructure()]);
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published' => fn() => Course::factory()->create(),
            'unpublished' => fn() => Course::factory()->unpublished()->create(),
            'banned' => fn() => Course::factory()->banned()->create(),
            'banned author' => fn() => Course::factory()->for(User::factory()->teacher()->banned(), 'author')->create(),
            'soft-deleted' => function () {
                $course = Course::factory()->create();
                $course->delete();
                return $course;
            }
        ]);

        it('fails if a banned user tries to retrieve a course', function () {
            $bannedUser = User::factory()->admin()->banned()->create();
            $course = Course::factory()->for($bannedUser, 'author')->create();

            Sanctum::actingAs($bannedUser);

            getJson(route('admin.courses.show', $course))
                ->assertForbidden();
        });
    });
})->group('course', 'admin');
