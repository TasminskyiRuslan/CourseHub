<?php

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CourseJsonStructure;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('CourseController -> store', function () {
    beforeEach(function () {
        $this->teacher = User::factory()->teacher()->create();
        $this->student = User::factory()->student()->create();
        $this->admin = User::factory()->admin()->create();
        $this->unverifiedTeacher = User::factory()->teacher()->unverified()->create();

        $this->makePayload = fn(array $overrides = []) => array_merge([
            'title'       => 'New Laravel Course',
            'slug'        => null,
            'description' => 'Comprehensive course on Laravel',
            'type'        => CourseType::ONLINE->value,
            'price'       => '199.99',
        ], $overrides);
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        beforeEach(function () {
            Sanctum::actingAs($this->teacher);
        });

        it('teacher creates a course', function () {
            $data = ($this->makePayload)();

            postJson(route('courses.store'), $data)
                ->assertCreated()
                ->assertJsonPath('data.title', $data['title'])
                ->assertJsonStructure(['data' => CourseJsonStructure::get()]);

            $this->assertDatabaseHas('courses', [
                'author_id' => $this->teacher->id,
                'title'     => $data['title'],
            ]);

            $this->assertDatabaseMissing('courses', [
                'author_id' => $this->teacher->id,
                'title'     => $data['title'],
                'slug' => null,
            ]);
        });

        it('teacher creates a course with custom slug', function () {
            $data = ($this->makePayload)(['slug' => 'custom-slug-for-course']);

            postJson(route('courses.store'), $data)
                ->assertCreated()
                ->assertJsonPath('data.slug', $data['slug'])
                ->assertJsonStructure(['data' => CourseJsonStructure::get()]);

            $this->assertDatabaseHas('courses', [
                'author_id' => $this->teacher->id,
                'slug'      => $data['slug'],
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
            postJson(route('courses.store'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'price', 'type']);
        });

        it('fails when price is invalid', function () {
            postJson(route('courses.store'), ($this->makePayload)(['price' => '-1.00']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['price']);
        });

        it('fails when type is invalid', function () {
            postJson(route('courses.store'), ($this->makePayload)(['type' => 'invalid-type']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['type']);
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

            postJson(route('courses.store'), ($this->makePayload)())
                ->assertForbidden();
        });

        it('forbids student', function () {
            Sanctum::actingAs($this->student);

            postJson(route('courses.store'), ($this->makePayload)())
                ->assertForbidden();
        });

        it('forbids unauthenticated user', function () {
            postJson(route('courses.store'), ($this->makePayload)())
                ->assertUnauthorized();
        });

        it('forbids teacher with unverified email', function () {
            Sanctum::actingAs($this->unverifiedTeacher);

            postJson(route('courses.store'), ($this->makePayload)())
                ->assertForbidden();
        });
    });
})->group('courses');
