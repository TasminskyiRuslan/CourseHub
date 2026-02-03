<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('CourseImageController -> update', function () {

    beforeEach(function () {
        Storage::fake('public');

        $this->teacher = User::factory()->teacher()->create();
        $this->otherTeacher = User::factory()->teacher()->create();
        $this->student = User::factory()->create();

        $this->course = Course::factory()
            ->for($this->teacher, 'author')
            ->create();

        $this->expectedCourseStructure = [
            'id',
            'author_id',
            'author' => [
                'id',
                'name',
                'slug',
                'email',
                'email_verified_at',
                'created_at',
                'updated_at',
            ],
            'title',
            'slug',
            'description',
            'type',
            'price',
            'image_url',
            'is_published',
            'created_at',
            'updated_at',
        ];
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */

    describe('success', function () {
        it('author updates course image', function () {
            Sanctum::actingAs($this->teacher);

            $file = UploadedFile::fake()->image('image.jpg');

            patchJson(route('courses.image.update', $this->course), ['image' => $file])
                ->assertOk()
                ->assertJsonStructure(['data' => $this->expectedCourseStructure]);

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
        it('fails when image is missing', function () {
            Sanctum::actingAs($this->teacher);

            patchJson(route('courses.image.update', $this->course), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails when file is not image', function () {
            Sanctum::actingAs($this->teacher);

            $file = UploadedFile::fake()->create('file.pdf');

            patchJson(route('courses.image.update', $this->course), ['image' => $file])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
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
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

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
