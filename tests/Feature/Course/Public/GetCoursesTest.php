<?php

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('Public -> CourseController -> index', function () {
    beforeEach(function () {
        Cache::flush();
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('allows users to retrieve only published, non-banned courses from active authors', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $activeCourses = Course::factory()->count(3)->create();
            $unpublishedCourses = Course::factory()->count(2)->unpublished()->create();
            $bannedCourses = Course::factory()->count(2)->banned()->create();
            $bannedAuthor = User::factory()->teacher()->banned()->create();
            $bannedAuthorCourses = Course::factory()->count(2)->for($bannedAuthor, 'author')->create();

            $response = getJson(route('courses.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => publicCourseJsonStructure()
                    ]
                ]);

            $responseDataIds = collect($response->json('data'))->pluck('id');

            foreach ($activeCourses as $course) {
                expect($responseDataIds)->toContain($course->id);
            }

            foreach ($unpublishedCourses->merge($bannedCourses)->merge($bannedAuthorCourses) as $hiddenCourse) {
                expect($responseDataIds)->not->toContain($hiddenCourse->id);
            }
        })->with([
            'guest' => null,
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | filters & sorting
    |--------------------------------------------------------------------------
    */
    describe('filters & sorting', function () {
        it('filters courses by a search string', function () {
            $course1 = Course::factory()->create(['title' => 'Laravel Deep Dive']);
            $course2 = Course::factory()->create(['title' => 'React Basics']);
            $searchString = substr($course1->title, 8);

            getJson(route('courses.index', ['filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonFragment(['id' => $course1->id])
                ->assertJsonMissing(['id' => $course2->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => publicCourseJsonStructure()
                    ]
                ]);
        });

        it('filters courses by type', function () {
            $onlineCourse = Course::factory()->create(['type' => CourseType::ONLINE]);
            $videoCourse = Course::factory()->create(['type' => CourseType::VIDEO]);

            getJson(route('courses.index', ['filter[type]' => CourseType::ONLINE->value]))
                ->assertOk()
                ->assertJsonFragment(['id' => $onlineCourse->id])
                ->assertJsonMissing(['id' => $videoCourse->id]);
        });

        it('filters courses by author slug', function () {
            $author = User::factory()->teacher()->create();
            $course1 = Course::factory()->for($author, 'author')->create();
            $course2 = Course::factory()->create();

            getJson(route('courses.index', ['filter[author]' => $author->slug]))
                ->assertOk()
                ->assertJsonFragment(['id' => $course1->id])
                ->assertJsonMissing(['id' => $course2->id]);
        });

        it('sorts courses by published_at (desc) by default', function () {
            $oldCourse = Course::factory()->create(['published_at' => now()->subDays(3)]);
            $newCourse = Course::factory()->create(['published_at' => now()->subDay()]);

            $response = getJson(route('courses.index'))->assertOk();
            $ids = collect($response->json('data'))->pluck('id')->all();

            expect(array_search($newCourse->id, $ids))->toBeLessThan(array_search($oldCourse->id, $ids));
        });

        it('sorts courses by published_at (asc and desc)', function () {
            $oldCourse = Course::factory()->create(['published_at' => now()->subDays(3)]);
            $newCourse = Course::factory()->create(['published_at' => now()->subDay()]);

            $ascResponse = getJson(route('courses.index', ['sort' => 'published_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldCourse->id, $ascIds))->toBeLessThan(array_search($newCourse->id, $ascIds));

            $descResponse = getJson(route('courses.index', ['sort' => '-published_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newCourse->id, $descIds))->toBeLessThan(array_search($oldCourse->id, $descIds));
        });

        it('sorts courses by title (asc and desc)', function () {
            $courseA = Course::factory()->create(['title' => 'Alpha Course']);
            $courseB = Course::factory()->create(['title' => 'Beta Course']);

            $ascResponse = getJson(route('courses.index', ['sort' => 'title']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($courseA->id, $ascIds))->toBeLessThan(array_search($courseB->id, $ascIds));

            $descResponse = getJson(route('courses.index', ['sort' => '-title']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($courseB->id, $descIds))->toBeLessThan(array_search($courseA->id, $descIds));
        });

        it('sorts courses by price (asc and desc)', function () {
            $cheap = Course::factory()->create(['price' => 150]);
            $expensive = Course::factory()->create(['price' => 450]);

            $ascResponse = getJson(route('courses.index', ['sort' => 'price']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($cheap->id, $ascIds))->toBeLessThan(array_search($expensive->id, $ascIds));

            $descResponse = getJson(route('courses.index', ['sort' => '-price']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($expensive->id, $descIds))->toBeLessThan(array_search($cheap->id, $descIds));
        });

        it('returns empty data when no courses match the search', function () {
            Course::factory()->create();

            getJson(route('courses.index', ['filter[search]' => 'non-existent-course-title']))
                ->assertOk()
                ->assertJsonCount(0, 'data');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('stores the course list in the cache after the first request', function () {
            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();

            getJson(route('courses.index'))->assertOk();

            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();
        });

        it('returns data from the cache instead of the database on subsequent requests', function () {
            $oldTitle = 'Cached Course Title';
            Course::factory()->create(['title' => $oldTitle]);

            getJson(route('courses.index'))->assertOk();

            $newTitle = 'Updated Course Title';
            DB::table('courses')->update(['title' => $newTitle]);

            getJson(route('courses.index'))
                ->assertOk()
                ->assertJsonFragment(['title' => $oldTitle])
                ->assertJsonMissing(['title' => $newTitle]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | pagination
    |--------------------------------------------------------------------------
    */
    describe('pagination', function () {
        it('returns a paginated list of courses', function () {
            Course::factory()->count(3)->create();

            getJson(route('courses.index'))
                ->assertOk()
                ->assertJsonStructure(paginationJsonStructure());
        });
    });
})->group('course', 'public');
