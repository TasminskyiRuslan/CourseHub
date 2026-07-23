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

describe('Teacher -> LessonController -> show', function () {
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

            $lesson = Lesson::factory()->create();

            getJson(route('teacher.courses.lessons.show', ['non-existing-slug', $lesson]))
                ->assertNotFound();
        });

        it('fails if the lesson does not exist', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();

            getJson(route('teacher.courses.lessons.show', [$course, 'non-existing-slug']))
                ->assertNotFound();
        });

        it('fails if the lesson is from another course', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();
            $lesson = Lesson::factory()->create();

            getJson(route('teacher.courses.lessons.show', [$course, $lesson]))
                ->assertNotFound();
        });

        it('fails if a non-author user tries to retrieve the course', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('teacher.courses.lessons.show', [$course, $lesson]))
                ->assertNotFound();
        })->with([
            'another teacher' => fn() => User::factory()->teacher()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ]);
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

            getJson(route('teacher.courses.lessons.show', [$course, $lesson]))
                ->assertUnauthorized();
        });

        it('fails if a user without permissions tries to retrieve the lesson', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('teacher.courses.lessons.show', [$course, $lesson]))
                ->assertForbidden();
        })->with([
            'user' => fn() => User::factory()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('allows an author to retrieve their own lesson', function ($userClosure, $courseClosure) {
            $user = $userClosure();

            Sanctum::actingAs($user);

            $courseInnerClosure = $courseClosure();
            $course = $courseInnerClosure($user);
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('teacher.courses.lessons.show', [$course, $lesson]))
                ->assertOk()
                ->assertJsonStructure(['data' => teacherLessonJsonStructure($course->type)]);
        })->with([
            'teacher' => fn() => User::factory()->teacher()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published' => fn() => fn($author) => Course::factory()->for($author, 'author')->create(),
            'unpublished' => fn() => fn($author) => Course::factory()->unpublished()->for($author, 'author')->create(),
            'banned' => fn() => fn($author) => Course::factory()->banned()->for($author, 'author')->create(),
        ]);

        it('fails if a banned user tries to retrieve their own lesson', function () {
            $bannedUser = User::factory()->teacher()->banned()->create();
            $course = Course::factory()->for($bannedUser, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            Sanctum::actingAs($bannedUser);

            getJson(route('teacher.courses.lessons.show', [$course, $lesson]))
                ->assertForbidden();
        });
    });
})->group('lesson', 'teacher');
