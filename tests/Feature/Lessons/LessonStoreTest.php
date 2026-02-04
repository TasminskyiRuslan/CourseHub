<?php

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\LessonJsonStructure;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('LessonsController -> store', function () {

    beforeEach(function () {
        $this->teacher = User::factory()->teacher()->create();
        $this->otherTeacher = User::factory()->teacher()->create();
        $this->student = User::factory()->student()->create();
        $this->admin = User::factory()->admin()->create();
        $this->unverifiedTeacher = User::factory()->teacher()->unverified()->create();

        $this->makePayload = function (?Course $course = null, array $overrides = []) {

            $typeSpecific = $course ? match ($course->type) {
                CourseType::OFFLINE => [
                    'start_time' => now()->addDay()->toIso8601String(),
                    'end_time'   => now()->addDay()->addHours(2)->toIso8601String(),
                    'address'    => 'Main Street 123',
                    'room_number'=> '101A',
                ],
                CourseType::ONLINE => [
                    'start_time'   => now()->addDay()->toIso8601String(),
                    'end_time'     => now()->addDay()->addHours(2)->toIso8601String(),
                    'meeting_link' => 'https://zoom.us/j/123456',
                ],
                CourseType::VIDEO => [
                    'video_url' => 'https://vimeo.com/123456',
                    'provider'  => 'vimeo',
                ],
            } : [];

            return array_merge([
                'title'    => 'New Lesson Title',
                'slug'     => null,
                'position' => null,
            ], $typeSpecific, $overrides);
        };
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {

        beforeEach(fn () => Sanctum::actingAs($this->teacher));

        it('creates an offline lesson', function () {
            $course = Course::factory()
                ->type(CourseType::OFFLINE)
                ->for($this->teacher, 'author')
                ->create();

            $data = ($this->makePayload)($course);

            postJson(route('courses.lessons.store', $course), $data)
                ->assertCreated()
                ->assertJsonPath('data.title', $data['title'])
                ->assertJsonStructure(['data' => LessonJsonStructure::get($course->type)]);

            $this->assertDatabaseHas('offline_lessons', [
                'address' => $data['address'],
                'room_number' => $data['room_number'],
            ]);
        });

        it('creates an online lesson', function () {
            $course = Course::factory()
                ->type(CourseType::ONLINE)
                ->for($this->teacher, 'author')
                ->create();

            $data = ($this->makePayload)($course);

            postJson(route('courses.lessons.store', $course), $data)
                ->assertCreated()
                ->assertJsonPath('data.title', $data['title'])
                ->assertJsonStructure(['data' => LessonJsonStructure::get($course->type)]);

            $this->assertDatabaseHas('online_lessons', [
                'meeting_link' => $data['meeting_link'],
            ]);
        });

        it('creates a video lesson', function () {
            $course = Course::factory()
                ->type(CourseType::VIDEO)
                ->for($this->teacher, 'author')
                ->create();

            $data = ($this->makePayload)($course);

            postJson(route('courses.lessons.store', $course), $data)
                ->assertCreated()
                ->assertJsonPath('data.title', $data['title'])
                ->assertJsonStructure(['data' => LessonJsonStructure::get($course->type)]);

            $this->assertDatabaseHas('video_lessons', [
                'video_url' => $data['video_url'],
            ]);
        });

        it('creates lesson with custom slug and position', function () {
            $course = Course::factory()
                ->type(CourseType::VIDEO)
                ->for($this->teacher, 'author')
                ->create();

            $data = ($this->makePayload)($course, [
                'slug' => 'custom-slug-for-lesson',
                'position' => 42,
            ]);

            postJson(route('courses.lessons.store', $course), $data)
                ->assertCreated()
                ->assertJsonPath('data.slug', $data['slug'])
                ->assertJsonPath('data.position', $data['position'])
                ->assertJsonStructure(['data' => LessonJsonStructure::get($course->type)]);

            $this->assertDatabaseHas('lessons', [
                'slug' => $data['slug'],
                'position' => $data['position'],
            ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {

        beforeEach(fn () => Sanctum::actingAs($this->teacher));

        it('fails when required fields missing', function () {
            $course = Course::factory()
                ->type(CourseType::OFFLINE)
                ->for($this->teacher, 'author')
                ->create();

            postJson(route('courses.lessons.store', $course), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title']);
        });

        it('returns not found for non-existing course', function () {
            postJson(
                route('courses.lessons.store', 'non-existing-slug'),
                ($this->makePayload)()
            )->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {

        beforeEach(function () {
            $this->course = Course::factory()
                ->type(CourseType::ONLINE)
                ->for($this->teacher, 'author')
                ->create();

            $this->data = ($this->makePayload)($this->course);
        });

        it('forbids admin', function () {
            Sanctum::actingAs($this->admin);

            postJson(route('courses.lessons.store', $this->course), $this->data)
                ->assertForbidden();
        });

        it('forbids non-author teacher', function () {
            Sanctum::actingAs($this->otherTeacher);

            postJson(route('courses.lessons.store', $this->course), $this->data)
                ->assertForbidden();
        });

        it('forbids teacher with unverified email', function () {
            Sanctum::actingAs($this->unverifiedTeacher);

            postJson(route('courses.lessons.store', $this->course), $this->data)
                ->assertForbidden();
        });

        it('forbids student', function () {
            Sanctum::actingAs($this->student);

            postJson(route('courses.lessons.store', $this->course), $this->data)
                ->assertForbidden();
        });

        it('forbids unauthenticated user', function () {
            postJson(route('courses.lessons.store', $this->course), $this->data)
                ->assertUnauthorized();
        });
    });
})->group('lessons');
