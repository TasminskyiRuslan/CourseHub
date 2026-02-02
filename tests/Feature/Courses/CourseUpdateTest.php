<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

describe('CourseController -> update', function () {

    beforeEach(function () {
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

        $this->payload = fn(array $overrides = []) => array_merge([
            'title' => 'Updated course title',
            'slug' => 'updated-course-title',
            'description' => 'Updated description',
            'price' => '299.99',
        ], $overrides);
    });

    describe('success', function () {
        beforeEach(function () {
            Sanctum::actingAs($this->teacher);
        });

        it('updates a course', function () {
            putJson(route('courses.update', $this->course), ($this->payload)())
                ->assertOk()
                ->assertJsonStructure(['data' => $this->expectedCourseStructure]);

            $data = ($this->payload)();

            $this->assertDatabaseHas('courses', [
                'id' => $this->course->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'price' => $data['price'],
            ]);
        });
    });

    describe('validation', function () {
        beforeEach(function () {
            Sanctum::actingAs($this->teacher);
        });
        it('fails when required fields are missing', function () {
            putJson(route('courses.update', $this->course), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'price']);
        });

        it('fails when price is invalid', function () {
            putJson(
                route('courses.update', $this->course),
                ($this->payload)(['price' => '-1'])
            )
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['price']);
        });

        it('returns not found for non-existing course', function () {
            Sanctum::actingAs($this->teacher);

            putJson(route('courses.update', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    describe('permissions', function () {
        it('forbids non-author teacher', function () {
            Sanctum::actingAs($this->otherTeacher);

            putJson(route('courses.update', $this->course), ($this->payload)())
                ->assertForbidden();
        });

        it('forbids student', function () {
            Sanctum::actingAs($this->student);

            putJson(route('courses.update', $this->course), ($this->payload)())
                ->assertForbidden();
        });

        it('forbids unauthenticated user', function () {
            putJson(route('courses.update', $this->course), ($this->payload)())
                ->assertUnauthorized();
        });
    });
})->group('courses');
