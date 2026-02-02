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

        Storage::disk('public')->put($this->course->image_path, 'dummy content');
    });

    describe('success', function () {
        beforeEach(function () {
            Sanctum::actingAs($this->teacher);
        });

        it('deletes a course', function () {
            deleteJson(route('courses.destroy', $this->course))
                ->assertNoContent();

            $this->assertDatabaseMissing('courses', ['id' => $this->course->id]);
            Storage::disk('public')->assertMissing($this->imagePath);
        });
    });

    describe('validation', function () {
        it('returns not found for non-existing course', function () {
        Sanctum::actingAs($this->teacher);

        deleteJson(route('courses.destroy', 'non-existing-slug'))
            ->assertNotFound();
        });
    });

    describe('permissions', function () {
        it('allows admin', function () {
            Sanctum::actingAs($this->admin);

            deleteJson(route('courses.destroy', $this->course))
                ->assertNoContent();
        });

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
