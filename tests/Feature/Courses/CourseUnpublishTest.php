<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('UnpublishCourseController', function () {

    beforeEach(function () {
        $this->teacher      = User::factory()->teacher()->create();
        $this->otherTeacher = User::factory()->teacher()->create();
        $this->student      = User::factory()->student()->create();
        $this->admin        = User::factory()->admin()->create();

        $this->course = Course::factory()
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

        it('author unpublishes course', function () {
            Sanctum::actingAs($this->teacher);

            patchJson(route('courses.unpublish', $this->course))
                ->assertNoContent();

            $this->assertFalse($this->course->fresh()->is_published);
        });

        it('admin unpublishes course', function () {
            Sanctum::actingAs($this->admin);

            patchJson(route('courses.unpublish', $this->course))
                ->assertNoContent();

            $this->assertFalse($this->course->fresh()->is_published);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {

        it('returns not found for non-existing course', function () {
            Sanctum::actingAs($this->teacher);

            patchJson(route('courses.unpublish', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {

        it('forbids non-author teacher', function () {
            Sanctum::actingAs($this->otherTeacher);

            patchJson(route('courses.unpublish', $this->course))
                ->assertForbidden();

            $this->assertTrue($this->course->fresh()->is_published);
        });

        it('forbids student', function () {
            Sanctum::actingAs($this->student);

            patchJson(route('courses.unpublish', $this->course))
                ->assertForbidden();

            $this->assertTrue($this->course->fresh()->is_published);
        });

        it('forbids unauthenticated user', function () {
            patchJson(route('courses.unpublish', $this->course))
                ->assertUnauthorized();

            $this->assertTrue($this->course->fresh()->is_published);
        });
    });

})->group('courses');
