<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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

            getJson(route('teacher.courses.show', 'non-existing-slug'))
                ->assertNotFound();
        });

        it('fails if non-author users try to retrieve the course', function (?User $user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            getJson(route('teacher.courses.show', $course))
                ->assertNotFound();
        })->with([
            'user' => fn () => User::factory()->create(),
            'another teacher' => fn () => User::factory()->teacher()->create(),
            'admin' => fn () => User::factory()->admin()->create(),
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

            getJson(route('teacher.courses.show', $course))
                ->assertUnauthorized();
        });

        it('allows a user to retrieve their own course', function (?User $user, Factory $courseFactory) {
            Sanctum::actingAs($user);

            $course = $courseFactory->for($user, 'author')->create();

            getJson(route('teacher.courses.show', $course))
                ->assertOk()
                ->assertJsonStructure(['data' => teacherCourseJsonStructure()]);
        })->with([
            'teacher' => fn () => User::factory()->teacher()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published course' => fn () => Course::factory(),
            'unpublished course' => fn () => Course::factory()->unpublished(),
            'banned course' => fn () => Course::factory()->banned(),
        ]);

        it('fails if a banned user tries to retrieve their own course', function () {
            $bannedAuthor = User::factory()->teacher()->banned()->create();
            $course = Course::factory()->for($bannedAuthor, 'author')->create();

            Sanctum::actingAs($bannedAuthor);

            getJson(route('teacher.courses.show', $course))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('correctly loads lessons_count for teacher course', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();
            Lesson::factory()->for($course)->count(3)->create();

            $response = getJson(route('teacher.courses.show', $course))
                ->assertOk()
                ->assertJsonFragment(['id' => $course->id]);

            expect($response->json('data.lessons_count'))->toBe(3);
        });
    });
})->group('course', 'teacher');
