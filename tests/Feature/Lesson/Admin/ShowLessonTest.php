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

describe('Admin -> LessonController -> show', function () {
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

            getJson(route('admin.courses.lessons.show', ['non-existing-slug', 'lesson-slug']))
                ->assertNotFound();
        });

        it('fails if the lesson does not exist', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();

            getJson(route('admin.courses.lessons.show', [$course->slug, 'non-existing-slug']))
                ->assertNotFound();
        });

        it('fails if the lesson is from another course', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->create();

            getJson(route('admin.courses.lessons.show', [$course, $lesson]))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to retrieve the lesson', function () {
            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('admin.courses.lessons.show', [$course, $lesson]))
                ->assertUnauthorized();
        });

        it('fails if a user without permissions tries to retrieve the lesson', function (?User $user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('admin.courses.lessons.show', [$course, $lesson]))
                ->assertForbidden();
        })->with([
            'user' => fn () => User::factory()->create(),
            'teacher' => fn () => User::factory()->teacher()->create(),
            'unverified teacher' => fn () => User::factory()->teacher()->unverified()->create(),
        ]);

        it('allows a user with permission to retrieve the lesson', function (?User $user, Course $course) {
            Sanctum::actingAs($user);

            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('admin.courses.lessons.show', [$course, $lesson]))
                ->assertOk()
                ->assertJsonStructure(['data' => adminLessonJsonStructure($course->type)]);
        })->with([
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published course' => fn () => Course::factory()->create(),
            'unpublished course' => fn () => Course::factory()->unpublished()->create(),
            'banned course' => fn () => Course::factory()->banned()->create(),
        ]);

        it('allows a user with permission to retrieve soft-deleted lesson', function (?User $user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            $lesson->delete();
            $course->delete();

            getJson(route('admin.courses.lessons.show', [$course, $lesson]))
                ->assertOk()
                ->assertJsonStructure(['data' => adminLessonJsonStructure($course->type)]);
        })->with([
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ]);

        it('fails if a banned user tries to retrieve the lesson', function () {
            $bannedAuthor = User::factory()->admin()->banned()->create();
            $course = Course::factory()->for($bannedAuthor, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            Sanctum::actingAs($bannedAuthor);

            getJson(route('admin.courses.lessons.show', [$course, $lesson]))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('correctly loads lessonable relation via LessonLoader', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            $response = getJson(route('admin.courses.lessons.show', [$course, $lesson]))
                ->assertOk()
                ->assertJsonFragment(['id' => $lesson->id]);

            expect($response->json('data.id'))->toBe($lesson->id);
        });
    });
})->group('lesson', 'admin');
