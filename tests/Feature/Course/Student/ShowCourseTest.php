<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Lesson;
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

        it('fails if student tries to retrieve an inactive course even if enrolled', function (Course $course) {
            $student = User::factory()->create();
            $course->students()->attach($student);

            Sanctum::actingAs($student);

            getJson(route('student.courses.show', $course))
                ->assertNotFound();
        })->with([
            'unpublished course' => fn () => Course::factory()->unpublished()->create(),
            'banned course' => fn () => Course::factory()->banned()->create(),
            'course of banned author' => fn () => Course::factory()->for(User::factory()->teacher()->banned(), 'author')->create(),
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

        it('allows a student to retrieve their enrolled active course', function (?User $user) {
            Sanctum::actingAs($user);

            $course = Course::factory()
                ->hasAttached($user, [], 'students')
                ->create();

            getJson(route('student.courses.show', $course))
                ->assertOk()
                ->assertJsonStructure(['data' => studentCourseJsonStructure()]);
        })->with([
            'user' => fn () => User::factory()->create(),
            'teacher' => fn () => User::factory()->teacher()->create(),
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ]);

        it('fails if a banned student tries to retrieve their enrolled course', function () {
            $bannedUser = User::factory()->banned()->create();
            $course = Course::factory()->hasAttached($bannedUser, [], 'students')->create();

            Sanctum::actingAs($bannedUser);

            getJson(route('student.courses.show', $course))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('correctly loads relations and counts active courses for author', function () {
            $student = User::factory()->create();
            $author = User::factory()->teacher()->create();

            Sanctum::actingAs($student);

            $course = Course::factory()
                ->for($author, 'author')
                ->hasAttached($student, [], 'students')
                ->create();

            Course::factory()->for($author, 'author')->create();
            Course::factory()->banned()->for($author, 'author')->create();

            Lesson::factory()->for($course)->count(2)->create();

            $response = getJson(route('student.courses.show', $course))
                ->assertOk()
                ->assertJsonFragment(['id' => $course->id]);

            expect($response->json('data.lessons_count'))->toBe(2)
                ->and($response->json('data.author.courses_count'))->toBe(2);
        });
    });
})->group('course', 'student');
