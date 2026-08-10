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
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            getJson(route('teacher.courses.lessons.show', [$course, 'non-existing-slug']))
                ->assertNotFound();
        });

        it('fails if the lesson is from another course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->create();

            getJson(route('teacher.courses.lessons.show', [$course, $lesson]))
                ->assertNotFound();
        });

        it('fails if a non-author user tries to retrieve the course', function (?User $user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('teacher.courses.lessons.show', [$course, $lesson]))
                ->assertNotFound();
        })->with([
            'another teacher' => fn () => User::factory()->teacher()->create(),
            'user' => fn () => User::factory()->create(),
            'unverified teacher' => fn () => User::factory()->teacher()->unverified()->create(),
            'admin' => fn () => User::factory()->admin()->create(),
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
            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('teacher.courses.lessons.show', [$course, $lesson]))
                ->assertUnauthorized();
        });

        it('allows an author to retrieve their own lesson', function (User $user, Factory $courseFactory) {
            Sanctum::actingAs($user);

            $course = $courseFactory->for($user, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('teacher.courses.lessons.show', [$course, $lesson]))
                ->assertOk()
                ->assertJsonStructure(['data' => teacherLessonJsonStructure($course->type)]);
        })->with([
            'teacher' => fn () => User::factory()->teacher()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published course' => fn () => Course::factory(),
            'unpublished course' => fn () => Course::factory()->unpublished(),
            'banned course' => fn () => Course::factory()->banned(),
        ]);

        it('fails if a banned user tries to retrieve their own lesson', function () {
            $bannedAuthor = User::factory()->teacher()->banned()->create();
            $course = Course::factory()->for($bannedAuthor, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            Sanctum::actingAs($bannedAuthor);

            getJson(route('teacher.courses.lessons.show', [$course, $lesson]))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('correctly loads lessonable relation via LessonLoader for teacher', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            $response = getJson(route('teacher.courses.lessons.show', [$course, $lesson]))
                ->assertOk()
                ->assertJsonFragment(['id' => $lesson->id]);

            expect($response->json('data.id'))->toBe($lesson->id);
        });
    });
})->group('lesson', 'teacher');
