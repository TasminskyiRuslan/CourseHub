<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('Public -> CourseController -> show', function () {
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
            getJson(route('course.show', 'non-existing-slug'))
                ->assertNotFound();
        });

        it('fails if users tries to retrieve a restricted course', function ($userClosure, $courseClosure) {
            $user = $userClosure ? $userClosure() : null;
            $course = $courseClosure();

            if ($user) {
                Sanctum::actingAs($user);
            }

            getJson(route('course.show', $course))
                ->assertNotFound();
        })->with([
            'guest' => null,
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'unpublished course' => fn() => Course::factory()->unpublished()->create(),
            'banned course' => fn() => Course::factory()->banned()->create(),
            'course of banned author' => fn() => Course::factory()->for(User::factory()->teacher()->banned(), 'author')->create(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('allows users to retrieve a published course from an active author', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create();

            getJson(route('course.show', $course))
                ->assertOk()
                ->assertJsonStructure(['data' => publicCourseJsonStructure()]);
        })->with([
            'guest' => null,
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ]);
    });
})->group('course', 'public');
