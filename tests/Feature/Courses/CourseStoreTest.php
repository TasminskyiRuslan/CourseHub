<?php

use App\Enums\CourseType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('CourseController -> store', function () {

    beforeEach(function () {
        $this->teacher = User::factory()->teacher()->create();
        $this->student = User::factory()->create();

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
            'title' => 'New Laravel Course',
            'slug' => null,
            'description' => 'Comprehensive course on Laravel',
            'type' => CourseType::ONLINE->value,
            'price' => '199.99',
        ], $overrides);
    });


    describe('success', function () {
        beforeEach(function () {
            Sanctum::actingAs($this->teacher);
        });

        it('creates a course', function () {
            postJson(route('courses.store'), ($this->payload)())
                ->assertCreated()
                ->assertJsonStructure(['data' => $this->expectedCourseStructure]);

            $data = ($this->payload)();

            $this->assertDatabaseHas('courses', [
                'author_id' => $this->teacher->id,
                'title' => $data['title'],
                'price' => $data['price'],
                'type' => $data['type'],
            ]);

            $this->assertDatabaseMissing('courses', [
                'title' => $data['title'],
                'slug' => null,
            ]);
        });

        it('creates a course with a custom manual slug', function () {
            $customSlug = 'custom-url-for-course';
            $data = ($this->payload)(['slug' => $customSlug]);

            postJson(route('courses.store'), $data)
                ->assertCreated()
                ->assertJsonPath('data.slug', $customSlug);

            $this->assertDatabaseHas('courses', [
                'author_id' => $this->teacher->id,
                'title' => $data['title'],
                'slug' => $customSlug,
                'price' => $data['price'],
                'type' => $data['type'],
            ]);
        });
    });

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

    describe('permissions', function () {
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
