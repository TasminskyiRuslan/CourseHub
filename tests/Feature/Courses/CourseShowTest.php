<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('CourseController -> show', function () {

    beforeEach(function () {
        $this->author = User::factory()->teacher()->create();
        $this->otherTeacher = User::factory()->teacher()->create();
        $this->admin = User::factory()->admin()->create();
        $this->student = User::factory()->create();

        $this->course = Course::factory()
            ->for($this->author, 'author')
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

    describe('success', function () {
        it('shows published course for unauthenticated', function () {
            $course = Course::factory()
                ->published()
                ->for($this->author, 'author')
                ->create();

            getJson(route('courses.show', $course))
                ->assertOk()
                ->assertJsonStructure(['data' => $this->expectedCourseStructure]);
        });

        it('shows unpublished course for author', function () {
            Sanctum::actingAs($this->author);

            getJson(route('courses.show', $this->course))
                ->assertOk()
                ->assertJsonStructure(['data' => $this->expectedCourseStructure]);
        });

        it('shows unpublished course for admin', function () {
            Sanctum::actingAs($this->admin);

            getJson(route('courses.show', $this->course))
                ->assertOk()
                ->assertJsonStructure(['data' => $this->expectedCourseStructure]);
        });
    });
    describe('validation', function () {
        it('returns not found for non-existing course', function () {
            getJson(route('courses.show', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    describe('permissions', function () {
        it('forbids unpublished course for non-author teacher', function () {
            Sanctum::actingAs($this->otherTeacher);

            getJson(route('courses.show', $this->course))
                ->assertForbidden();
        });

        it('forbids unpublished course for student', function () {
            Sanctum::actingAs($this->student);

            getJson(route('courses.show', $this->course))
                ->assertForbidden();
        });

        it('forbids unpublished course for unauthenticated user', function () {
            getJson(route('courses.show', $this->course))
                ->assertForbidden();
        });
    });
})->group('courses');
