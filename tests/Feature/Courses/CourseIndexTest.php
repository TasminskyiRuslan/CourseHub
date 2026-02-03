<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CourseJsonStructure;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('CourseController -> index', function () {
    beforeEach(function () {
        $this->author = User::factory()->teacher()->create();
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('returns only published courses', function () {
            Course::factory()
                ->count(3)
                ->published()
                ->for($this->author, 'author')
                ->create();

            Course::factory()
                ->count(2)
                ->unpublished()
                ->for($this->author, 'author')
                ->create();

            getJson(route('courses.index'))
                ->assertOk()
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => CourseJsonStructure::get(),
                    ]
                ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | filtering & sorting
    |--------------------------------------------------------------------------
    */
    describe('filters & sorting', function () {
        it('filters courses by search', function () {
            $firstTitle = 'Laravel Advanced';
            $secondTitle = 'Vue Basics';

            Course::factory()->published()->for($this->author, 'author')->create(['title' => $firstTitle]);
            Course::factory()->published()->for($this->author, 'author')->create(['title' => $secondTitle]);

            $partialFirst = substr($firstTitle, 0, 6);

            getJson(route('courses.index', ['filter[search]' => $partialFirst]))
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.title', $firstTitle)
                ->assertJsonStructure([
                    'data' => [
                        '*' => CourseJsonStructure::get(),
                    ],
                ]);
        });

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
                        '*' => CourseJsonStructure::get(),
                    ]
                ]);
        });

        it('sorts courses by price desc', function () {
            $prices = ['100.00', '200.00', '300.00'];

            Course::factory()
                ->count(3)
                ->published()
                ->for($this->author, 'author')
                ->state(new Sequence(
                    ...array_map(fn($price) => ['price' => $price], $prices)
                ))
                ->create();

            getJson(route('courses.index', ['sort' => '-price']))
                ->assertOk()
                ->assertJsonPath('data.*.price', array_reverse($prices))
                ->assertJsonStructure([
                    'data' => [
                        '*' => CourseJsonStructure::get(),
                    ]
                ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('returns empty for non-existing author slug', function () {
            getJson(route('courses.index', ['filter[author]' => 'non-existing-slug']))
                ->assertOk()
                ->assertJsonCount(0, 'data');
        });

        it('returns empty for unmatched search', function () {
            getJson(route('courses.index', ['filter[search]' => 'NonExistingCourse']))
                ->assertOk()
                ->assertJsonCount(0, 'data');
        });
    });
})->group('courses');
