<?php

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

describe('Student -> LessonController -> show', function () {
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
            $student = User::factory()->create();
            Sanctum::actingAs($student);

            $lesson = Lesson::factory()->create();

            getJson(route('student.courses.lessons.show', ['non-existing-slug', $lesson]))
                ->assertNotFound();
        });

        it('fails if the lesson does not exist', function () {
            $student = User::factory()->create();
            Sanctum::actingAs($student);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $student->enrolledCourses()->attach($course, [], 'enrolledCourses');

            getJson(route('student.courses.lessons.show', [$course, 'non-existing-slug']))
                ->assertNotFound();
        });

        it('fails if the lesson is from another course', function () {
            $student = User::factory()->create();
            Sanctum::actingAs($student);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $student->enrolledCourses()->attach($course, [], 'enrolledCourses');

            $otherCourse = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($otherCourse, 'course')->create();

            getJson(route('student.courses.lessons.show', [$course, $lesson]))
                ->assertNotFound();
        });

        it('fails if a non-enrolled user tries to retrieve the lesson', function ($user) {
            Sanctum::actingAs($user);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('student.courses.lessons.show', [$course, $lesson]))
                ->assertNotFound();
        })->with([
            'other student' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
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
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('student.courses.lessons.show', [$course, $lesson]))
                ->assertUnauthorized();
        });

        it('fails if a user without permissions tries to retrieve the lesson', function ($user) {
            Sanctum::actingAs($user);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('student.courses.lessons.show', [$course, $lesson]))
                ->assertForbidden();
        })->with([
            'unverified student' => fn() => User::factory()->unverified()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
        ]);

        it('allows enrolled students to retrieve their lesson', function ($userClosure, $courseClosure) {
            $user = $userClosure();
            Sanctum::actingAs($user);

            $courseInnerClosure = $courseClosure();
            $course = $courseInnerClosure();

            $user->enrolledCourses()->attach($course, [], 'enrolledCourses');

            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('student.courses.lessons.show', [$course, $lesson]))
                ->assertOk()
                ->assertJsonStructure(['data' => studentLessonJsonStructure($course->type)]);
        })->with([
            'student' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published' => fn() => fn() => Course::factory()->create(),
        ]);

        it('fails if a student tries to retrieve a lesson of an inactive course', function ($courseClosure) {
            $student = User::factory()->create();
            Sanctum::actingAs($student);

            $author = User::factory()->teacher()->create();
            $course = $courseClosure($author);
            $lesson = Lesson::factory()->for($course, 'course')->create();

            $student->enrolledCourses()->attach($course, [], 'enrolledCourses');

            getJson(route('student.courses.lessons.show', [$course, $lesson]))
                ->assertNotFound();
        })->with([
            'unpublished' => fn() => fn($author) => Course::factory()->unpublished()->for($author, 'author')->create(),
            'banned' => fn() => fn($author) => Course::factory()->banned()->for($author, 'author')->create(),
        ]);

        it('fails if a banned user tries to retrieve a lesson of their enrolled course', function () {
            $bannedUser = User::factory()->banned()->create();
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            $bannedUser->enrolledCourses()->attach($course, [], 'enrolledCourses');

            Sanctum::actingAs($bannedUser);

            getJson(route('student.courses.lessons.show', [$course, $lesson]))
                ->assertForbidden();
        });
    });
})->group('lesson', 'student');
