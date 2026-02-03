<?php

use App\Enums\CourseType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CourseJsonStructure;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('CourseController -> store', function () {
    beforeEach(function () {
        $this->teacher = User::factory()
            ->teacher()
            ->create();
        $this->student = User::factory()
            ->student()
            ->create();
        $this->admin = User::factory()
            ->admin()
            ->create();

        $this->payload = fn(array $overrides = []) => array_merge([
            'title' => 'New Laravel Course',
            'slug' => null,
            'description' => 'Comprehensive course on Laravel',
            'type' => CourseType::ONLINE->value,
            'price' => '199.99',
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
            $data = ($this->payload)();

            postJson(route('courses.store'), $data)
                ->assertCreated()
                ->assertJsonStructure(['data' => CourseJsonStructure::get()]);

            $this->assertDatabaseHas('courses', [
                'author_id' => $this->teacher->id,
                'title' => $data['title'],
                'price' => $data['price'],
                'type' => $data['type'],
            ]);
        });

        it('teacher creates a course with custom slug', function () {
            $customSlug = 'custom-slug-for-course';
            $data = ($this->payload)(['slug' => $customSlug]);

            postJson(route('courses.store'), $data)
                ->assertCreated()
                ->assertJsonPath('data.slug', $customSlug)
                ->assertJsonStructure(['data' => CourseJsonStructure::get()]);

            $this->assertDatabaseHas('courses', [
                'author_id' => $this->teacher->id,
                'title' => $data['title'],
                'slug' => $customSlug,
                'price' => $data['price'],
                'type' => $data['type'],
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
            postJson(route('courses.store'), ($this->payload)(['price' => '-1']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['price']);
        });

        it('fails when type is invalid', function () {
            postJson(route('courses.store'), ($this->payload)(['type' => 'INVALID']))
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

            postJson(route('courses.store'), ($this->payload)())
                ->assertForbidden();
        });

        it('forbids authenticated student', function () {
            Sanctum::actingAs($this->student);

            postJson(route('courses.store'), ($this->payload)())
                ->assertForbidden();
        });

        it('forbids unauthenticated user', function () {
            postJson(route('courses.store'), ($this->payload)())
                ->assertUnauthorized();
        });
    });
})->group('courses');
