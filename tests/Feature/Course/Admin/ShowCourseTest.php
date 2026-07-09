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

            getJson(route('admin.course.show', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if unauthenticated users try to retrieve the course', function () {
            $course = Course::factory()->create();

            getJson(route('admin.course.show', $course))
                ->assertUnauthorized();
        });

        it('fails if users without permissions try to retrieve the course', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            getJson(route('admin.course.show', $course))
                ->assertForbidden();
        })->with([
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows users without to retrieve the courses', function ($userClosure, $courseClosure) {
            $user = $userClosure();
            Sanctum::actingAs($user);

            $course = $courseClosure();

            getJson(route('admin.course.show', $course))
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
    });
})->group('course', 'admin');
