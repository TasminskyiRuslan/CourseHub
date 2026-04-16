<?php

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('LessonsController -> destroy', function () {
    beforeEach(function () {
        $this->teacher = User::factory()->teacher()->create();
        $this->otherTeacher = User::factory()->teacher()->create();
        $this->student = User::factory()->student()->create();
        $this->admin = User::factory()->admin()->create();

        $this->offlineCourse = Course::factory()->type(CourseType::OFFLINE)->for($this->teacher, 'author')->create();
        $this->onlineCourse = Course::factory()->type(CourseType::ONLINE)->for($this->teacher, 'author')->create();
        $this->videoCourse = Course::factory()->type(CourseType::VIDEO)->for($this->teacher, 'author')->create();

        $this->offlineLesson = Lesson::factory()->for($this->offlineCourse)->create();
        $this->onlineLesson = Lesson::factory()->for($this->onlineCourse)->create();
        $this->videoLesson = Lesson::factory()->for($this->videoCourse)->create();
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('author deletes offline lesson', function () {
            Sanctum::actingAs($this->teacher);

            deleteJson(route('courses.lessons.destroy', [
                'course' => $this->offlineCourse,
                'lesson' => $this->offlineLesson,
            ]))->assertNoContent();

            $this->assertDatabaseMissing('lessons', [
                'id' => $this->offlineLesson->id,
            ]);

            $this->assertDatabaseMissing('offline_lessons', [
                'id' => $this->offlineLesson->lessonable->id,
            ]);
        });

        it('author deletes online lesson', function () {
            Sanctum::actingAs($this->teacher);

            deleteJson(route('courses.lessons.destroy', [
                'course' => $this->onlineCourse,
                'lesson' => $this->onlineLesson,
            ]))->assertNoContent();

            $this->assertDatabaseMissing('lessons', [
                'id' => $this->onlineLesson->id,
            ]);

            $this->assertDatabaseMissing('online_lessons', [
                'id' => $this->onlineLesson->lessonable->id,
            ]);
        });

        it('author deletes video lesson', function () {
            Sanctum::actingAs($this->teacher);

            deleteJson(route('courses.lessons.destroy', [
                'course' => $this->videoCourse,
                'lesson' => $this->videoLesson,
            ]))->assertNoContent();

            $this->assertDatabaseMissing('lessons', [
                'id' => $this->videoLesson->id,
            ]);

            $this->assertDatabaseMissing('video_lessons', [
                'id' => $this->videoLesson->lessonable->id,
            ]);
        });

        it('admin deletes any lesson', function () {
            Sanctum::actingAs($this->admin);

            deleteJson(route('courses.lessons.destroy', [
                'course' => $this->videoCourse,
                'lesson' => $this->videoLesson,
            ]))->assertNoContent();

            $this->assertDatabaseMissing('lessons', [
                'id' => $this->videoLesson->id,
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
            Sanctum::actingAs($this->teacher);

            deleteJson(route('courses.lessons.destroy', [
                'course' => $this->offlineCourse,
                'lesson' => 'non-existing-slug',
            ]))->assertNotFound();
        });

        it('returns not found for non-existing course', function () {
            Sanctum::actingAs($this->teacher);

            deleteJson(route('courses.lessons.destroy', [
                'course' => 'non-existing-course',
                'lesson' => $this->offlineLesson,
            ]))->assertNotFound();
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

            deleteJson(route('courses.lessons.destroy', [
                'course' => $this->offlineCourse,
                'lesson' => $this->offlineLesson,
            ]))->assertForbidden();
        });

        it('forbids student', function () {
            Sanctum::actingAs($this->student);

            deleteJson(route('courses.lessons.destroy', [
                'course' => $this->offlineCourse,
                'lesson' => $this->offlineLesson,
            ]))->assertForbidden();
        });

        it('forbids unauthenticated user', function () {
            deleteJson(route('courses.lessons.destroy', [
                'course' => $this->offlineCourse,
                'lesson' => $this->offlineLesson,
            ]))->assertUnauthorized();
        });
    });
})->group('lessons');
