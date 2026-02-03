<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('CourseImageController -> destroy', function () {

    beforeEach(function () {
        $this->teacher = User::factory()->teacher()->create();
        $this->otherTeacher = User::factory()->teacher()->create();
        $this->student = User::factory()->create();
        $this->admin = User::factory()->admin()->create();

        Storage::fake('public');

        $this->imagePath = 'courses/test-image.jpg';

        $this->course = Course::factory()
            ->for($this->teacher, 'author')
            ->create([
                'image_path' => $this->imagePath,
            ]);

        Storage::disk('public')->put($this->course->image_path, 'fake');
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {

        it('author deletes course image', function () {
            Sanctum::actingAs($this->teacher);

            deleteJson(route('courses.image.destroy', $this->course))
                ->assertNoContent();

            $this->assertDatabaseHas('courses', [
                'id' => $this->course->id,
                'image_path' => null,
            ]);

            Storage::disk('public')->assertMissing($this->imagePath);
        });

        it('does nothing if image does not exist', function () {
            Sanctum::actingAs($this->teacher);

            $this->course->update(['image_path' => null]);

            deleteJson(route('courses.image.destroy', $this->course))
                ->assertNoContent();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {

        it('forbids admin', function () {
            Sanctum::actingAs($this->admin);

            deleteJson(route('courses.image.destroy', $this->course))
                ->assertForbidden();

            $this->assertDatabaseHas('courses', [
                'id' => $this->course->id,
                'image_path' => $this->imagePath,
            ]);
        });

        it('forbids non-author teacher', function () {
            Sanctum::actingAs($this->otherTeacher);

            deleteJson(route('courses.image.destroy', $this->course))
                ->assertForbidden();
        });

        it('forbids student', function () {
            Sanctum::actingAs($this->student);

            deleteJson(route('courses.image.destroy', $this->course))
                ->assertForbidden();
        });

        it('forbids unauthenticated user', function () {
            deleteJson(route('courses.image.destroy', $this->course))
                ->assertUnauthorized();
        });
    });
})->group('courses');
