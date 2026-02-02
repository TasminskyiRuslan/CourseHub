<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('CourseController -> index', function () {

    beforeEach(function () {
        $this->author = User::factory()->teacher()->create();

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
        it('returns only published courses', function () {
            Course::factory()
                ->count(3)
                ->published()
                ->for($this->author, 'author')
                ->create();

            Course::factory()
                ->count(2)
                ->for($this->author, 'author')
                ->create();

            getJson(route('courses.index'))
                ->assertOk()
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => $this->expectedCourseStructure,
                    ]
                ]);
        })->group('courses');

        it('filters courses by search', function () {
            Course::factory()
                ->published()
                ->for($this->author, 'author')
                ->create(['title' => 'Laravel Advanced']);

            Course::factory()
                ->published()
                ->for($this->author, 'author')
                ->create(['title' => 'Vue Basics']);

            getJson(route('courses.index', ['filter[search]' => 'Laravel']))
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => $this->expectedCourseStructure,
                    ]
                ]);
        })->group('courses');

        it('sorts courses by price desc', function () {
            Course::factory()
                ->count(3)
                ->published()
                ->for($this->author, 'author')
                ->state(new Sequence(
                    ['price' => '100.00'],
                    ['price' => '300.00'],
                    ['price' => '200.00'],
                ))
                ->create();

            getJson(route('courses.index', ['sort' => '-price']))
                ->assertOk()
                ->assertJsonPath('data.0.price', '300.00')
                ->assertJsonPath('data.1.price', '200.00')
                ->assertJsonPath('data.2.price', '100.00')
                ->assertJsonStructure([
                    'data' => [
                        '*' => $this->expectedCourseStructure,
                    ]
                ]);
        })->group('courses');

        it('filters courses by author slug', function () {
            Course::factory()
                ->published()
                ->for($this->author, 'author')
                ->create();

            Course::factory()
                ->published()
                ->create();

            getJson(route('courses.index', ['filter[author]' => $this->author->slug]))
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => $this->expectedCourseStructure,
                    ]
                ]);
        })->group('courses');
    });
});
