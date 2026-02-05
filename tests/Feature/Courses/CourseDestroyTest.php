<?php

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('CourseController -> destroy', function () {
    beforeEach(function () {
        $this->author      = User::factory()->teacher()->create();
        $this->otherTeacher = User::factory()->teacher()->create();
        $this->student      = User::factory()->student()->create();
        $this->admin        = User::factory()->admin()->create();

        Storage::fake('public');

        $this->imagePath = 'courses/test-image.jpg';

        $this->course = Course::factory()->unpublished()->withImage($this->imagePath)->for($this->author, 'author')->create();
        $this->lessons = Lesson::factory()->for($this->course)->count(8)->create();

        Storage::disk('public')->put($this->course->image_path, 'fake');
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('author deletes course', function () {
            Sanctum::actingAs($this->author);

            deleteJson(route('courses.destroy', $this->course))
                ->assertNoContent();

            $this->assertDatabaseMissing('courses', [
                'id' => $this->course->id,
            ]);

            foreach ($this->lessons as $lesson) {
                $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);

                $lessonableTable = match ($this->course->type) {
                    CourseType::OFFLINE => 'offline_lessons',
                    CourseType::ONLINE => 'online_lessons',
                    CourseType::VIDEO => 'video_lessons',
                };

                $this->assertDatabaseMissing($lessonableTable, [
                    'id' => $lesson->lessonable->id,
                ]);
            }

            Storage::disk('public')->assertMissing($this->imagePath);
        });

        it('admin deletes course', function () {
            Sanctum::actingAs($this->admin);

            deleteJson(route('courses.destroy', $this->course))
                ->assertNoContent();

            $this->assertDatabaseMissing('courses', [
                'id' => $this->course->id,
            ]);

            foreach ($this->lessons as $lesson) {
                $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);

                $lessonableTable = match ($this->course->type) {
                    CourseType::OFFLINE => 'offline_lessons',
                    CourseType::ONLINE => 'online_lessons',
                    CourseType::VIDEO => 'video_lessons',
                };

                $this->assertDatabaseMissing($lessonableTable, [
                    'id' => $lesson->lessonable->id,
                ]);
            }

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
            Sanctum::actingAs($this->author);

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
