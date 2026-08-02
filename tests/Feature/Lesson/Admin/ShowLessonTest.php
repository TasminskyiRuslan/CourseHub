<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        it('fails if a user without permissions tries to retrieve the lesson', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('admin.courses.lessons.show', [$course, $lesson]))
                ->assertForbidden();
        })->with([
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
        ]);

        it('allows a user with permission to retrieve the lesson', function ($userClosure, $courseClosure) {
            $user = $userClosure();
            Sanctum::actingAs($user);

            $courseInnerClosure = $courseClosure();
            $course = $courseInnerClosure($user);
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('admin.courses.lessons.show', [$course, $lesson]))
                ->assertOk()
                ->assertJsonStructure(['data' => adminLessonJsonStructure($course->type)]);
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published' => fn() => fn($author) => Course::factory()->create(),
            'unpublished' => fn() => fn($author) => Course::factory()->unpublished()->create(),
            'banned' => fn() => fn($author) => Course::factory()->banned()->create(),
        ]);

        it('allows a user with permission to retrieve soft-deleted lesson', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            $lesson->delete();
            $course->delete();

            getJson(route('admin.courses.lessons.show', [$course, $lesson]))
                ->assertOk()
                ->assertJsonStructure(['data' => adminLessonJsonStructure($course->type)]);
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
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
})->group('lesson', 'admin');
