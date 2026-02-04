<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('CourseController -> destroy', function () {

    beforeEach(function () {
        $this->teacher      = User::factory()->teacher()->create();
        $this->otherTeacher = User::factory()->teacher()->create();
        $this->student      = User::factory()->student()->create();
        $this->admin        = User::factory()->admin()->create();

        Storage::fake('public');

        $this->imagePath = 'courses/test-image.jpg';

        $this->course = Course::factory()
            ->unpublished()
            ->withImage($this->imagePath)
            ->for($this->teacher, 'author')
            ->create();

        Storage::disk('public')->put($this->course->image_path, 'fake');
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {

        it('author deletes course with image', function () {

            Sanctum::actingAs($this->teacher);

            deleteJson(route('courses.destroy', $this->course))
                ->assertNoContent();

            $this->assertDatabaseMissing('courses', [
                'id' => $this->course->id,
            ]);

            Storage::disk('public')->assertMissing($this->imagePath);
        });

        it('admin deletes course with image', function () {

            Sanctum::actingAs($this->admin);

            deleteJson(route('courses.destroy', $this->course))
                ->assertNoContent();

            $this->assertDatabaseMissing('courses', [
                'id' => $this->course->id,
            ]);

            Storage::disk('public')->assertMissing($this->imagePath);
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

            deleteJson(route('courses.destroy', 'non-existing-slug'))
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

            deleteJson(route('courses.destroy', $this->course))
                ->assertForbidden();
        });

        it('forbids student', function () {

            Sanctum::actingAs($this->student);

            deleteJson(route('courses.destroy', $this->course))
                ->assertForbidden();
        });

        it('forbids unauthenticated user', function () {

            deleteJson(route('courses.destroy', $this->course))
                ->assertUnauthorized();
        });
    });

})->group('courses');
