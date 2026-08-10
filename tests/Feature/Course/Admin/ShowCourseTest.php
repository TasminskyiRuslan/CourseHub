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

        it('fails if a user without permissions tries to retrieve a course', function (?User $user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            getJson(route('admin.courses.show', $course))
                ->assertForbidden();
        })->with([
            'user' => fn () => User::factory()->create(),
            'teacher' => fn () => User::factory()->teacher()->create(),
        ]);

        it('allows a user with permissions to retrieve a course', function (?User $user, Course $course) {
            Sanctum::actingAs($user);

            getJson(route('admin.courses.show', $course))
                ->assertOk()
                ->assertJsonStructure(['data' => adminCourseJsonStructure()]);
        })->with([
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published' => fn () => Course::factory()->create(),
            'unpublished' => fn () => Course::factory()->unpublished()->create(),
            'banned' => fn () => Course::factory()->banned()->create(),
            'banned author' => fn () => Course::factory()->for(User::factory()->teacher()->banned(), 'author')->create(),
            'soft-deleted' => function () {
                $course = Course::factory()->create();
                $course->delete();

                return $course;
            },
        ]);

        it('fails if a banned user tries to retrieve a course', function () {
            $bannedAdmin = User::factory()->admin()->banned()->create();
            $course = Course::factory()->create();

            Sanctum::actingAs($bannedAdmin);

            getJson(route('admin.courses.show', $course))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('correctly loads soft-deleted author and counts soft-deleted relations', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();

            $otherCourse = Course::factory()->for($author, 'author')->create();
            $otherCourse->delete();

            Lesson::factory()->for($course)->create();
            $trashedLesson = Lesson::factory()->for($course)->create();
            $trashedLesson->delete();

            $author->delete();

            $response = getJson(route('admin.courses.show', $course))
                ->assertOk()
                ->assertJsonFragment(['id' => $course->id]);

            expect($response->json('data.author.id'))->toBe($author->id)
                ->and($response->json('data.lessons_count'))->toBe(2)
                ->and($response->json('data.author.courses_count'))->toBe(2);
        });
    });
})->group('course', 'admin');
