<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CourseJsonStructure;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('CourseImageController -> update', function () {
    beforeEach(function () {
        Storage::fake('public');

        $this->author      = User::factory()->teacher()->create();
        $this->otherTeacher = User::factory()->teacher()->create();
        $this->student      = User::factory()->student()->create();
        $this->admin        = User::factory()->admin()->create();

        $this->course = Course::factory()->unpublished()->for($this->author, 'author')->create();
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('author updates course image', function () {
            Sanctum::actingAs($this->author);

            $file = UploadedFile::fake()->image('image.jpg');

            patchJson(route('courses.image.update', $this->course), ['image' => $file])
                ->assertOk()
                ->assertJsonStructure([
                    'data' => CourseJsonStructure::get(),
                ]);

            $course = $this->course->fresh();

            expect($course->image_path)->not->toBeNull();

            Storage::disk('public')->assertExists($course->image_path);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        beforeEach(function () {
            Sanctum::actingAs($this->author);
        });

        it('fails when image is missing', function () {
            patchJson(route('courses.image.update', $this->course), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails when file is not image', function () {
            $file = UploadedFile::fake()->create('file.pdf');

            patchJson(route('courses.image.update', $this->course), ['image' => $file])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('returns not found for non-existing course', function () {
            patchJson(route('courses.image.update', 'non-existing-slug'))
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

            $file = UploadedFile::fake()->image('image.jpg');

            patchJson(route('courses.image.update', $this->course), ['image' => $file])
                ->assertForbidden();
        });

        it('forbids student', function () {
            Sanctum::actingAs($this->student);

            $file = UploadedFile::fake()->image('image.jpg');

            patchJson(route('courses.image.update', $this->course), ['image' => $file])
                ->assertForbidden();
        });

        it('forbids admin', function () {
            Sanctum::actingAs($this->admin);

            $file = UploadedFile::fake()->image('image.jpg');

            patchJson(route('courses.image.update', $this->course), ['image' => $file])
                ->assertForbidden();
        });

        it('forbids unauthenticated user', function () {
            $file = UploadedFile::fake()->image('image.jpg');

            patchJson(route('courses.image.update', $this->course), ['image' => $file])
                ->assertUnauthorized();
        });
    });
})->group('courses');
