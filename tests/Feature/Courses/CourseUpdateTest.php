<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CourseJsonStructure;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

describe('CourseController -> update', function () {
    beforeEach(function () {
        $this->teacher = User::factory()
            ->teacher()
            ->create();
        $this->otherTeacher = User::factory()
            ->teacher()
            ->create();
        $this->admin = User::factory()
            ->admin()
            ->create();
        $this->student = User::factory()
            ->student()
            ->create();

        $this->course = Course::factory()
            ->unpublished()
            ->for($this->teacher, 'author')
            ->create();

        $this->payload = fn(array $overrides = []) => array_merge([
            'title' => 'Updated course title',
            'slug' => 'updated-course-title',
            'description' => 'Updated description',
            'price' => '299.99',
        ], $overrides);
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('author updates a course', function () {
            Sanctum::actingAs($this->teacher);

            $data = ($this->payload)();

            putJson(route('courses.update', $this->course), $data)
                ->assertOk()
                ->assertJsonStructure(['data' => CourseJsonStructure::get()]);

            $this->assertDatabaseHas('courses', [
                'id' => $this->course->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'price' => $data['price'],
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
            Sanctum::actingAs($this->teacher);
        });

        it('fails when required fields are missing', function () {
            putJson(route('courses.update', $this->course), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'price']);
        });

        it('fails when price is invalid', function () {
            putJson(route('courses.update', $this->course), ($this->payload)(['price' => '-1']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['price']);
        });

        it('returns not found for non-existing course', function () {
            putJson(route('courses.update', 'non-existing-slug'), ($this->payload)())
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

            putJson(route('courses.update', $this->course), ($this->payload)())
                ->assertForbidden();
        });

        it('forbids student', function () {
            Sanctum::actingAs($this->student);

            putJson(route('courses.update', $this->course), ($this->payload)())
                ->assertForbidden();
        });

        it('forbids admin', function () {
            Sanctum::actingAs($this->admin);

            putJson(route('courses.update', $this->course), ($this->payload)())
                ->assertForbidden();
        });

        it('forbids unauthenticated user', function () {
            putJson(route('courses.update', $this->course), ($this->payload)())
                ->assertUnauthorized();
        });
    });

})->group('courses');
