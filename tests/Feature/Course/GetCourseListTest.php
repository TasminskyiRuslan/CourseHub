<?php

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('CourseController -> index', function () {
    beforeEach(function () {
        Cache::flush();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('allows users to retrieve only published courses', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $publishedCourses = Course::factory()->count(3)->create();
            $unpublishedCourses = Course::factory()->count(2)->unpublished()->create();

            getJson(route('course.index'))
                ->assertOk()
                ->assertJsonCount($publishedCourses->count(), 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => courseJsonStructure()
                    ]
                ]);
        })->with([
            'guest' => null,
            'unverified' => fn() => User::factory()->unverified()->create(),
            'student' => fn() => User::factory()->student()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows the author to retrieve their own unpublished courses', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $publishedCourses = Course::factory()->count(2)->create();
            $ownUnpublishedCourses = Course::factory()->count(2)->unpublished()->for($author, 'author')->create();
            $unpublishedCourses = Course::factory()->unpublished()->create();

            getJson(route('course.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => courseJsonStructure()
                    ]
                ])
                ->assertJsonCount($publishedCourses->count() + $ownUnpublishedCourses->count(), 'data');
        });

        it('allows users with permissions to retrieve all unpublished courses', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $publishedCourses = Course::factory()->count(2)->create();
            $unpublishedCourses = Course::factory()->count(3)->unpublished()->create();

            getJson(route('course.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => courseJsonStructure()
                    ]
                ])
                ->assertJsonCount($publishedCourses->count() + $unpublishedCourses->count(), 'data');
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
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
            $searchString = substr($course1->title, 7);

            getJson(route('course.index', ['filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment(['id' => $course1->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => courseJsonStructure()
                    ]
                ]);
        });

        it('filters courses by author slug', function () {
            $author = User::factory()->create();
            $course1 = Course::factory()->for($author, 'author')->create();
            $course2 = Course::factory()->create();

            getJson(route('course.index', ['filter[author]' => $author]))
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment(['id' => $course1->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => courseJsonStructure()
                    ]
                ]);
        });

        it('sorts courses by created_at (desc) by default', function () {
            $oldCourse = Course::factory()->create();
            $oldCourse->setCreatedAt(now()->subDays(2))->save();
            $newCourse = Course::factory()->create();
            $newCourse->setCreatedAt(now()->subDay())->save();

            getJson(route('course.index'))
                ->assertOk()
                ->assertJsonPath('data.0.id', $newCourse->id)
                ->assertJsonPath('data.1.id', $oldCourse->id)
                ->assertJsonStructure([
                    'data' => [
                        '*' => courseJsonStructure()
                    ]
                ]);
        });

        it('sorts courses by created_at (asc and desc)', function () {
            $oldCourse = Course::factory()->create();
            $oldCourse->setCreatedAt(now()->subDays(2))->save();
            $newCourse = Course::factory()->create();
            $newCourse->setCreatedAt(now()->subDay())->save();

            getJson(route('course.index', ['sort' => 'created_at']))
                ->assertOk()
                ->assertJsonFragment(['id' => $oldCourse->id])
                ->assertJsonFragment(['id' => $newCourse->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => courseJsonStructure()
                    ]
                ]);
            getJson(route('course.index', ['sort' => '-created_at']))
                ->assertOk()
                ->assertJsonFragment(['id' => $newCourse->id])
                ->assertJsonFragment(['id' => $oldCourse->id])
                ->assertJsonStructure([
                    'data' => [
                        '*' => courseJsonStructure()
                    ]
                ]);
        });

        it('sorts courses by title (asc and desc)', function () {
            $courseA = Course::factory()->create(['title' => 'CourseA']);
            $courseB = Course::factory()->create(['title' => 'CourseB']);
            $courseC = Course::factory()->create(['title' => 'CourseC']);

            getJson(route('course.index', ['sort' => 'title']))
                ->assertOk()
                ->assertJsonPath('data.0.id', $courseA->id)
                ->assertJsonPath('data.1.id', $courseB->id)
                ->assertJsonPath('data.2.id', $courseC->id)
                ->assertJsonStructure([
                    'data' => [
                        '*' => courseJsonStructure()
                    ]
                ]);
            getJson(route('course.index', ['sort' => '-title']))
                ->assertOk()
                ->assertJsonPath('data.0.id', $courseC->id)
                ->assertJsonPath('data.1.id', $courseB->id)
                ->assertJsonPath('data.2.id', $courseA->id)
                ->assertJsonStructure([
                    'data' => [
                        '*' => courseJsonStructure()
                    ]
                ]);
        });

        it('sorts courses by price (asc and desc)', function () {
            $cheap = Course::factory()->create(['price' => 100]);
            $expensive = Course::factory()->create(['price' => 500]);

            getJson(route('course.index', ['sort' => 'price']))
                ->assertOk()
                ->assertJsonPath('data.0.id', $cheap->id)
                ->assertJsonPath('data.1.id', $expensive->id)
                ->assertJsonStructure([
                    'data' => [
                        '*' => courseJsonStructure()
                    ]
                ]);
            getJson(route('course.index', ['sort' => '-price']))
                ->assertOk()
                ->assertJsonPath('data.0.id', $expensive->id)
                ->assertJsonPath('data.1.id', $cheap->id)
                ->assertJsonStructure([
                    'data' => [
                        '*' => courseJsonStructure()
                    ]
                ]);
        });

        it('includes author, lessons_count by using the include query parameter', function () {
            $courses = Course::factory()->type(CourseType::OFFLINE)->count(3)->create();

            getJson(route('course.index', ['include' => 'author,lessons_count']))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => courseJsonStructure(withAuthor: true, withLessonsCount: true)
                    ]
                ]);
        });

        it('returns empty data when no courses match the search', function () {
            $course = Course::factory()->create();

            getJson(route('course.index', ['filter[search]' => 'non-existent']))
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
            Cache::spy();

            getJson(route('course.index'))->assertOk();
            Cache::shouldHaveReceived('tags')
                ->with([config('cache.tags.course_list')])
                ->once();
        });

        it('returns data from the cache instead of the database on subsequent requests', function () {
            $oldTitle = 'CourseA';
            Course::factory()->create(['title' => $oldTitle]);

            getJson(route('course.index'))->assertOk();

            $newTitle = 'CourseB';
            DB::table('courses')->update(['title' => $newTitle]);

            getJson(route('course.index'))
                ->assertOk()
                ->assertJsonFragment(['title' => $oldTitle])
                ->assertJsonMissing(['title' => $newTitle]);
        });

        it('does not store the course list in the cache users with permissions', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            Cache::spy();

            getJson(route('course.index'))->assertOk();
            Cache::shouldNotHaveReceived('tags');
        })->with([
            'teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | pagination
    |--------------------------------------------------------------------------
    */
    describe('pagination', function () {
        it('returns a paginated list of courses', function () {
            $courses = Course::factory()->count(7)->create();

            getJson(route('course.index'))
                ->assertOk()
                ->assertJsonStructure(paginationJsonStructure());
        });
    });
})->group('course');
