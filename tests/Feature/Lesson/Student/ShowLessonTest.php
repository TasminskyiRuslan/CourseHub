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
            $student->enrolledCourses()->attach($course);

            getJson(route('student.courses.lessons.show', [$course, 'non-existing-slug']))
                ->assertNotFound();
        });

        it('fails if the lesson is from another course', function () {
            $student = User::factory()->create();
            Sanctum::actingAs($student);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $student->enrolledCourses()->attach($course);

            $otherCourse = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($otherCourse, 'course')->create();

            getJson(route('student.courses.lessons.show', [$course, $lesson]))
                ->assertNotFound();
        });

        it('fails if a non-enrolled user tries to retrieve the lesson', function (?User $user) {
            Sanctum::actingAs($user);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('student.courses.lessons.show', [$course, $lesson]))
                ->assertNotFound();
        })->with([
            'another student' => fn () => User::factory()->create(),
            'teacher' => fn () => User::factory()->teacher()->create(),
            'unverified student' => fn () => User::factory()->unverified()->create(),
            'unverified teacher' => fn () => User::factory()->teacher()->unverified()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
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

        it('allows enrolled students to retrieve their lesson', function (?User $user, Course $course) {
            Sanctum::actingAs($user);

            $user->enrolledCourses()->attach($course);

            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('student.courses.lessons.show', [$course, $lesson]))
                ->assertOk()
                ->assertJsonStructure(['data' => studentLessonJsonStructure($course->type)]);
        })->with([
            'student' => fn () => User::factory()->create(),
            'teacher' => fn () => User::factory()->teacher()->create(),
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published course' => fn () => Course::factory()->create(),
        ]);

        it('fails if a student tries to retrieve a lesson of an inactive course', function (Course $course) {
            $student = User::factory()->create();
            $student->enrolledCourses()->attach($course);

            Sanctum::actingAs($student);

            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('student.courses.lessons.show', [$course, $lesson]))
                ->assertNotFound();
        })->with([
            'unpublished course' => fn () => Course::factory()->unpublished()->create(),
            'banned course' => fn () => Course::factory()->banned()->create(),
        ]);

        it('fails if a banned user tries to retrieve a lesson of their enrolled course', function () {
            $bannedUser = User::factory()->banned()->create();
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            $bannedUser->enrolledCourses()->attach($course);

            Sanctum::actingAs($bannedUser);

            getJson(route('student.courses.lessons.show', [$course, $lesson]))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('correctly loads lessonable relation via LessonLoader for student', function () {
            $student = User::factory()->create();
            Sanctum::actingAs($student);

            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            $student->enrolledCourses()->attach($course);

            $lesson = Lesson::factory()->for($course, 'course')->create();

            $response = getJson(route('student.courses.lessons.show', [$course, $lesson]))
                ->assertOk()
                ->assertJsonFragment(['id' => $lesson->id]);

            expect($response->json('data.id'))->toBe($lesson->id);
        });
    });
})->group('lesson', 'student');
