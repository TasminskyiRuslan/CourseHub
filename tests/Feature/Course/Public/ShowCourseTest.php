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
            getJson(route('courses.show', 'non-existing-slug'))
                ->assertNotFound();
        });

        it('fails if a user tries to retrieve a restricted course', function (?User $user, Course $course) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            getJson(route('courses.show', $course))
                ->assertNotFound();
        })->with([
            'guest' => null,
            'user' => fn () => User::factory()->create(),
            'teacher' => fn () => User::factory()->teacher()->create(),
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
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
        it('allows a user to retrieve a published course from an active author', function (?User $user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create();

            getJson(route('courses.show', $course))
                ->assertOk()
                ->assertJsonStructure(['data' => publicCourseJsonStructure()]);
        })->with([
            'guest' => null,
            'user' => fn () => User::factory()->create(),
            'teacher' => fn () => User::factory()->teacher()->create(),
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('correctly loads relations and counts active courses for author', function () {
            $author = User::factory()->teacher()->create();

            $course = Course::factory()->for($author, 'author')->create();

            Course::factory()->for($author, 'author')->create();
            Course::factory()->banned()->for($author, 'author')->create();

            Lesson::factory()->for($course)->count(2)->create();

            $response = getJson(route('courses.show', $course))
                ->assertOk()
                ->assertJsonFragment(['id' => $course->id]);

            expect($response->json('data.lessons_count'))->toBe(2)
                ->and($response->json('data.author.courses_count'))->toBe(2);
        });
    });
})->group('course', 'public');
