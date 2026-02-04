<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CourseJsonStructure;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('CourseController -> show', function () {

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
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {

        it('shows published course for unauthenticated user', function () {

            getJson(route('courses.show', $this->publishedCourse))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => CourseJsonStructure::get(),
                ]);
        });

        it('author sees unpublished course', function () {

            Sanctum::actingAs($this->teacher);

            getJson(route('courses.show', $this->unpublishedCourse))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => CourseJsonStructure::get(),
                ]);
        });

        it('admin sees unpublished course', function () {

            Sanctum::actingAs($this->admin);

            getJson(route('courses.show', $this->unpublishedCourse))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => CourseJsonStructure::get(),
                ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {

        it('returns not found for non-existing course', function () {

            getJson(route('courses.show', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {

        it('forbids unpublished course for non-author teacher', function () {

            Sanctum::actingAs($this->otherTeacher);

            getJson(route('courses.show', $this->unpublishedCourse))
                ->assertForbidden();
        });

        it('forbids unpublished course for student', function () {

            Sanctum::actingAs($this->student);

            getJson(route('courses.show', $this->unpublishedCourse))
                ->assertForbidden();
        });

        it('forbids unpublished course for unauthenticated user', function () {

            getJson(route('courses.show', $this->unpublishedCourse))
                ->assertForbidden();
        });
    });

})->group('courses');
