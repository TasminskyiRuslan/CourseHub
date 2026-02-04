<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\LessonJsonStructure;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('LessonsController -> index', function () {

    beforeEach(function () {
        $this->teacher       = User::factory()->teacher()->create();
        $this->otherTeacher = User::factory()->teacher()->create();
        $this->admin        = User::factory()->admin()->create();
        $this->student      = User::factory()->student()->create();

        $this->publishedCourse   = Course::factory()->published()->for($this->teacher, 'author')->create();
        $this->unpublishedCourse = Course::factory()->unpublished()->for($this->teacher, 'author')->create();

        Lesson::factory()->count(3)->for($this->publishedCourse, 'course')->create();
        Lesson::factory()->count(2)->for($this->unpublishedCourse, 'course')->create();
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {

        it('lists lessons for published course for unauthenticated user', function () {
            getJson(route('courses.lessons.index', $this->publishedCourse))
                ->assertOk()
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => LessonJsonStructure::get($this->publishedCourse->type),
                    ]
                ]);
        });

        it('lists lessons for unpublished course for author', function () {
            Sanctum::actingAs($this->teacher);

            getJson(route('courses.lessons.index', $this->unpublishedCourse))
                ->assertOk()
                ->assertJsonCount(2, 'data');
        });

        it('lists lessons for unpublished course for admin', function () {
            Sanctum::actingAs($this->admin);

            getJson(route('courses.lessons.index', $this->unpublishedCourse))
                ->assertOk()
                ->assertJsonCount(2, 'data');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | filtering & sorting
    |--------------------------------------------------------------------------
    */
    describe('filters & sorting', function () {

        beforeEach(function () {
            $this->course    = Course::factory()->published()->for($this->teacher, 'author')->create();
            $this->titles    = ['Introduction in Laravel', 'Advanced Vue Concepts', 'Testing with PestPHP'];
            $this->positions = [1, 2, 3];

            Lesson::factory()
                ->count(3)
                ->for($this->course, 'course')
                ->state(new Sequence(
                    ...array_map(fn($title, $position) => ['title' => $title, 'position' => $position], $this->titles, $this->positions)
                ))
                ->create();
        });

        it('filters lessons by search', function () {
            $partial = substr($this->titles[0], 0, 12);

            getJson(route('courses.lessons.index', [
                'course'       => $this->course,
                'filter[search]' => $partial
            ]))
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.title', $this->titles[0]);
        });

        it('sorts lessons by position desc', function () {
            getJson(route('courses.lessons.index', [
                'course' => $this->course,
                'sort'   => '-position'
            ]))
                ->assertOk()
                ->assertJsonPath('data.*.position', array_reverse($this->positions));
        });

        it('sorts lessons by position asc', function () {
            getJson(route('courses.lessons.index', [
                'course' => $this->course,
                'sort'   => 'position'
            ]))
                ->assertOk()
                ->assertJsonPath('data.*.position', $this->positions);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {

        it('returns not found for non-existing course slug', function () {
            getJson(route('courses.lessons.index', 'non-existing-slug'))
                ->assertNotFound();
        });

        it('returns empty for unmatched search', function () {
            $course = Course::factory()->published()->for($this->teacher, 'author')->create();

            getJson(route('courses.lessons.index', $course, ['filter[search]' => 'NonExistingLesson']))
                ->assertOk()
                ->assertJsonCount(0, 'data');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {

        it('forbids unpublished course for other teacher', function () {
            Sanctum::actingAs($this->otherTeacher);

            getJson(route('courses.lessons.index', $this->unpublishedCourse))
                ->assertForbidden();
        });

        it('forbids unpublished course for student', function () {
            Sanctum::actingAs($this->student);

            getJson(route('courses.lessons.index', $this->unpublishedCourse))
                ->assertForbidden();
        });

        it('forbids unpublished course for unauthenticated user', function () {
            getJson(route('courses.lessons.index', $this->unpublishedCourse))
                ->assertForbidden();
        });
    });

})->group('lessons');
