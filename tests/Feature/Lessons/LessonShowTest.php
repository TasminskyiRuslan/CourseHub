<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\LessonJsonStructure;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('LessonsController -> show', function () {

    beforeEach(function () {
        $this->teacher       = User::factory()->teacher()->create();
        $this->otherTeacher = User::factory()->teacher()->create();
        $this->admin        = User::factory()->admin()->create();
        $this->student      = User::factory()->student()->create();

        $this->unpublishedCourse = Course::factory()
            ->unpublished()
            ->for($this->teacher, 'author')
            ->create();

        $this->publishedCourse = Course::factory()
            ->published()
            ->for($this->teacher, 'author')
            ->create();

        $this->unpublishedLesson = Lesson::factory()
            ->for($this->unpublishedCourse)
            ->create();

        $this->publishedLesson = Lesson::factory()
            ->for($this->publishedCourse)
            ->create();
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {

        it('shows published lesson for unauthenticated user', function () {

            getJson(route('courses.lessons.show', [
                'course' => $this->publishedCourse->slug,
                'lesson' => $this->publishedLesson->slug,
            ]))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => LessonJsonStructure::get($this->publishedCourse->type),
                ]);
        });

        it('author sees unpublished lesson', function () {

            Sanctum::actingAs($this->teacher);

            getJson(route('courses.lessons.show', [
                'course' => $this->unpublishedCourse->slug,
                'lesson' => $this->unpublishedLesson->slug,
            ]))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => LessonJsonStructure::get($this->unpublishedCourse->type),
                ]);
        });

        it('admin sees unpublished lesson', function () {

            Sanctum::actingAs($this->admin);

            getJson(route('courses.lessons.show', [
                'course' => $this->unpublishedCourse->slug,
                'lesson' => $this->unpublishedLesson->slug,
            ]))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => LessonJsonStructure::get($this->unpublishedCourse->type),
                ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {

        it('returns not found for non-existing lesson', function () {

            getJson(route('courses.lessons.show', [
                'course' => $this->publishedCourse->slug,
                'lesson' => 'non-existing-slug',
            ]))
                ->assertNotFound();
        });

        it('returns not found for non-existing course', function () {

            getJson(route('courses.lessons.show', [
                'course' => 'non-existing-course',
                'lesson' => $this->publishedLesson->slug,
            ]))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {

        it('forbids unpublished lesson for non-author teacher', function () {

            Sanctum::actingAs($this->otherTeacher);

            getJson(route('courses.lessons.show', [
                'course' => $this->unpublishedCourse->slug,
                'lesson' => $this->unpublishedLesson->slug,
            ]))
                ->assertForbidden();
        });

        it('forbids unpublished lesson for student', function () {

            Sanctum::actingAs($this->student);

            getJson(route('courses.lessons.show', [
                'course' => $this->unpublishedCourse->slug,
                'lesson' => $this->unpublishedLesson->slug,
            ]))
                ->assertForbidden();
        });

        it('forbids unpublished lesson for unauthenticated user', function () {

            getJson(route('courses.lessons.show', [
                'course' => $this->unpublishedCourse->slug,
                'lesson' => $this->unpublishedLesson->slug,
            ]))
                ->assertForbidden();
        });
    });

})->group('lessons');
