<?php

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\LessonJsonStructure;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

describe('LessonsController -> update', function () {
    beforeEach(function () {
        $this->author       = User::factory()->teacher()->create();
        $this->otherTeacher  = User::factory()->teacher()->create();
        $this->student       = User::factory()->student()->create();
        $this->admin         = User::factory()->admin()->create();

        $this->makePayload = function (Course $course, array $overrides = []) {
            $typeSpecific = match ($course->type) {
                CourseType::OFFLINE => [
                    'start_time'  => now()->addDay()->toIso8601String(),
                    'end_time'    => now()->addDay()->addHours(2)->toIso8601String(),
                    'address'     => 'Updated Street 456',
                    'room_number' => '202B',
                ],
                CourseType::ONLINE => [
                    'start_time'   => now()->addDay()->toIso8601String(),
                    'end_time'     => now()->addDay()->addHours(2)->toIso8601String(),
                    'meeting_link' => 'https://zoom.us/j/654321',
                ],
                CourseType::VIDEO => [
                    'video_url' => 'https://vimeo.com/654321',
                    'provider'  => 'youtube',
                ],
            };

            return array_merge([
                'title'    => 'Updated Lesson Title',
                'slug'     => 'updated-lesson-slug',
                'position' => 1,
            ], $typeSpecific, $overrides);
        };
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        beforeEach(function () {
            Sanctum::actingAs($this->author);
        });

        it('updates an offline lesson', function () {
            $course = Course::factory()->type(CourseType::OFFLINE)->for($this->author, 'author')->create();

            $lesson = Lesson::factory()->for($course)->create();

            $data = ($this->makePayload)($course);

            putJson(route('courses.lessons.update', [
                'course' => $course,
                'lesson' => $lesson,
            ]), $data)
                ->assertOk()
                ->assertJsonPath('data.slug', $data['slug'])
                ->assertJsonStructure(['data' => LessonJsonStructure::get($course->type)]);

            $this->assertDatabaseHas('lessons', [
                'id' => $lesson->id,
                'course_id' => $course->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'position' => $data['position'],
            ]);

            $this->assertDatabaseHas('offline_lessons', [
                'id' => $lesson->lessonable->id,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'address' => $data['address'],
                'room_number' => $data['room_number'],
            ]);
        });

        it('updates an online lesson', function () {
            $course = Course::factory()->type(CourseType::ONLINE)->for($this->author, 'author')->create();

            $lesson = Lesson::factory()->for($course)->create();

            $data = ($this->makePayload)($course);

            putJson(route('courses.lessons.update', [
                'course' => $course,
                'lesson' => $lesson,
            ]), $data)
                ->assertOk()
                ->assertJsonPath('data.slug', $data['slug'])
                ->assertJsonStructure(['data' => LessonJsonStructure::get($course->type)]);

            $this->assertDatabaseHas('lessons', [
                'id' => $lesson->id,
                'course_id' => $course->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'position' => $data['position'],
            ]);

            $this->assertDatabaseHas('online_lessons', [
                'id' => $lesson->lessonable->id,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'meeting_link' => $data['meeting_link'],
            ]);
        });

        it('updates a video lesson', function () {
            $course = Course::factory()->type(CourseType::VIDEO)->for($this->author, 'author')->create();

            $lesson = Lesson::factory()->for($course)->create();

            $data = ($this->makePayload)($course);

            putJson(route('courses.lessons.update', [
                'course' => $course,
                'lesson' => $lesson,
            ]), $data)
                ->assertOk()
                ->assertJsonPath('data.slug', $data['slug'])
                ->assertJsonStructure(['data' => LessonJsonStructure::get($course->type)]);

            $this->assertDatabaseHas('lessons', [
                'id' => $lesson->id,
                'course_id' => $course->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'position' => $data['position'],
            ]);

            $this->assertDatabaseHas('video_lessons', [
                'id' => $lesson->lessonable->id,
                'video_url' => $data['video_url'],
                'provider'  => $data['provider'],
            ]);
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

            $this->course = Course::factory()->for($this->author, 'author')->create();
            $this->lesson = Lesson::factory()->for($this->course)->create();
        });

        it('fails when required fields missing', function () {
            putJson(route('courses.lessons.update', [
                'course' => $this->course,
                'lesson' => $this->lesson,
            ]), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug']);
        });

        it('returns not found for non-existing lesson', function () {
            putJson(route('courses.lessons.update', [
                'course' => $this->course,
                'lesson' => 'non-existing-slug',
            ]), ($this->makePayload)($this->course))
                ->assertNotFound();
        });

        it('returns not found for non-existing course', function () {
            putJson(route('courses.lessons.update', [
                'course' => 'non-existing-course',
                'lesson' => $this->lesson,
            ]), ($this->makePayload)($this->course))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        beforeEach(function () {
            $this->course = Course::factory()->for($this->author, 'author')->create();
            $this->lesson = Lesson::factory()->for($this->course)->create();
        });

        it('forbids admin', function () {
            Sanctum::actingAs($this->admin);

            putJson(route('courses.lessons.update', [
                'course' => $this->course,
                'lesson' => $this->lesson,
            ]), ($this->makePayload)($this->course))
                ->assertForbidden();
        });

        it('forbids non-author teacher', function () {
            Sanctum::actingAs($this->otherTeacher);

            putJson(route('courses.lessons.update', [
                'course' => $this->course,
                'lesson' => $this->lesson,
            ]), ($this->makePayload)($this->course))
                ->assertForbidden();
        });

        it('forbids student', function () {
            Sanctum::actingAs($this->student);

            putJson(route('courses.lessons.update', [
                'course' => $this->course,
                'lesson' => $this->lesson,
            ]), ($this->makePayload)($this->course))
                ->assertForbidden();
        });

        it('forbids unauthenticated user', function () {
            putJson(route('courses.lessons.update', [
                'course' => $this->course,
                'lesson' => $this->lesson,
            ]), ($this->makePayload)($this->course))
                ->assertUnauthorized();
        });
    });
})->group('lessons');
