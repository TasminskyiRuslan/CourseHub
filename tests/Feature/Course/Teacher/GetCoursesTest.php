<?php

declare(strict_types=1);

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('Teacher -> CourseController -> index', function () {
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
        it('fails if an unauthenticated user tries to retrieve courses', function () {
            getJson(route('teacher.courses.index'))
                ->assertUnauthorized();
        });

        it('fails if a user without permissions tries to retrieve courses', function (?User $user) {
            Sanctum::actingAs($user);

            Course::factory()->count(5)->create();

            getJson(route('teacher.courses.index'))
                ->assertForbidden();
        })->with([
            'user' => fn () => User::factory()->create(),
            'admin' => fn () => User::factory()->admin()->create(),
        ]);

        it('allows a user to retrieve their own courses', function (?User $user) {
            Sanctum::actingAs($user);

            $ownCourses = Course::factory()->count(3)->for($user, 'author')->create();
            Course::factory()->count(2)->create();

            $response = getJson(route('teacher.courses.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => teacherCourseJsonStructure(),
                    ],
                ]);

            $responseDataIds = collect($response->json('data'))->pluck('id');
            foreach ($ownCourses as $course) {
                expect($responseDataIds)->toContain($course->id);
            }
        })->with([
            'teacher' => fn () => User::factory()->teacher()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ]);

        it('fails if a banned user tries to retrieve their own courses', function () {
            $bannedAuthor = User::factory()->teacher()->banned()->create();
            $ownCourses = Course::factory()->count(3)->for($bannedAuthor, 'author')->create();

            Sanctum::actingAs($bannedAuthor);

            getJson(route('teacher.courses.index'))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | filters & sorting
    |--------------------------------------------------------------------------
    */
    describe('filters & sorting', function () {
        it('filters courses by a search string in title', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course1 = Course::factory()->for($author, 'author')->create(['title' => 'Laravel Deep Dive']);
            $course2 = Course::factory()->for($author, 'author')->create(['title' => 'React Basics']);
            $searchString = substr($course1->title, 8);

            getJson(route('teacher.courses.index', ['filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonFragment(['id' => $course1->id])
                ->assertJsonMissing(['data' => [['id' => $course2->id]]])
                ->assertJsonStructure([
                    'data' => [
                        '*' => teacherCourseJsonStructure(),
                    ],
                ]);
        });

        it('filters courses by description in search query', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course1 = Course::factory()->for($author, 'author')->create(['description' => 'Unique description text']);
            $course2 = Course::factory()->for($author, 'author')->create(['description' => 'Standard content']);

            getJson(route('teacher.courses.index', ['filter[search]' => 'Unique description']))
                ->assertOk()
                ->assertJsonFragment(['id' => $course1->id])
                ->assertJsonMissing(['data' => [['id' => $course2->id]]]);
        });

        it('filters courses by type', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $onlineCourse = Course::factory()->type(CourseType::ONLINE)->for($author, 'author')->create();
            $videoCourse = Course::factory()->type(CourseType::VIDEO)->for($author, 'author')->create();

            getJson(route('teacher.courses.index', ['filter[type]' => CourseType::ONLINE->value]))
                ->assertOk()
                ->assertJsonFragment(['id' => $onlineCourse->id])
                ->assertJsonMissing(['data' => [['id' => $videoCourse->id]]]);
        });

        it('filters courses by banned status', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $bannedCourse = Course::factory()->for($author, 'author')->banned()->create();
            $activeCourse = Course::factory()->for($author, 'author')->create();

            getJson(route('teacher.courses.index', ['filter[banned]' => 'true']))
                ->assertOk()
                ->assertJsonFragment(['id' => $bannedCourse->id])
                ->assertJsonMissing(['data' => [['id' => $activeCourse->id]]]);

            getJson(route('teacher.courses.index', ['filter[banned]' => 'false']))
                ->assertOk()
                ->assertJsonFragment(['id' => $activeCourse->id])
                ->assertJsonMissing(['data' => [['id' => $bannedCourse->id]]]);
        });

        it('filters courses by published status', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $publishedCourse = Course::factory()->for($author, 'author')->create();
            $unpublishedCourse = Course::factory()->for($author, 'author')->unpublished()->create();

            getJson(route('teacher.courses.index', ['filter[published]' => 'true']))
                ->assertOk()
                ->assertJsonFragment(['id' => $publishedCourse->id])
                ->assertJsonMissing(['data' => [['id' => $unpublishedCourse->id]]]);

            getJson(route('teacher.courses.index', ['filter[published]' => 'false']))
                ->assertOk()
                ->assertJsonFragment(['id' => $unpublishedCourse->id])
                ->assertJsonMissing(['data' => [['id' => $publishedCourse->id]]]);
        });

        it('sorts courses by created_at (desc) by default', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $oldCourse = Course::factory()->for($author, 'author')->create();
            $oldCourse->setCreatedAt(now()->subDays(2))->save();
            $newCourse = Course::factory()->for($author, 'author')->create();
            $newCourse->setCreatedAt(now()->subDay())->save();

            $response = getJson(route('teacher.courses.index'))->assertOk();
            $ids = collect($response->json('data'))->pluck('id')->all();

            expect(array_search($newCourse->id, $ids))->toBeLessThan(array_search($oldCourse->id, $ids));
        });

        it('sorts courses by created_at (asc and desc)', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $oldCourse = Course::factory()->for($author, 'author')->create();
            $oldCourse->setCreatedAt(now()->subDays(2))->save();
            $newCourse = Course::factory()->for($author, 'author')->create();
            $newCourse->setCreatedAt(now()->subDay())->save();

            $ascResponse = getJson(route('teacher.courses.index', ['sort' => 'created_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldCourse->id, $ascIds))->toBeLessThan(array_search($newCourse->id, $ascIds));

            $descResponse = getJson(route('teacher.courses.index', ['sort' => '-created_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newCourse->id, $descIds))->toBeLessThan(array_search($oldCourse->id, $descIds));
        });

        it('sorts courses by published_at (asc and desc)', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $oldCourse = Course::factory()->for($author, 'author')->create(['published_at' => now()->subDays(3)]);
            $newCourse = Course::factory()->for($author, 'author')->create(['published_at' => now()->subDay()]);

            $ascResponse = getJson(route('teacher.courses.index', ['sort' => 'published_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldCourse->id, $ascIds))->toBeLessThan(array_search($newCourse->id, $ascIds));

            $descResponse = getJson(route('teacher.courses.index', ['sort' => '-published_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newCourse->id, $descIds))->toBeLessThan(array_search($oldCourse->id, $descIds));
        });

        it('sorts courses by title (asc and desc)', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $courseA = Course::factory()->for($author, 'author')->create(['title' => 'Alpha Course']);
            $courseB = Course::factory()->for($author, 'author')->create(['title' => 'Beta Course']);

            $ascResponse = getJson(route('teacher.courses.index', ['sort' => 'title']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($courseA->id, $ascIds))->toBeLessThan(array_search($courseB->id, $ascIds));

            $descResponse = getJson(route('teacher.courses.index', ['sort' => '-title']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($courseB->id, $descIds))->toBeLessThan(array_search($courseA->id, $descIds));
        });

        it('sorts courses by price (asc and desc)', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $cheap = Course::factory()->for($author, 'author')->create(['price' => 150]);
            $expensive = Course::factory()->for($author, 'author')->create(['price' => 450]);

            $ascResponse = getJson(route('teacher.courses.index', ['sort' => 'price']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($cheap->id, $ascIds))->toBeLessThan(array_search($expensive->id, $ascIds));

            $descResponse = getJson(route('teacher.courses.index', ['sort' => '-price']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($expensive->id, $descIds))->toBeLessThan(array_search($cheap->id, $descIds));
        });

        it('sorts courses by lessons_count (asc and desc)', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $courseWithManyLessons = Course::factory()->for($author, 'author')->hasLessons(5)->create();
            $courseWithFewLessons = Course::factory()->for($author, 'author')->hasLessons(1)->create();

            $ascResponse = getJson(route('teacher.courses.index', ['sort' => 'lessons_count']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($courseWithFewLessons->id, $ascIds))->toBeLessThan(array_search($courseWithManyLessons->id, $ascIds));

            $descResponse = getJson(route('teacher.courses.index', ['sort' => '-lessons_count']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($courseWithManyLessons->id, $descIds))->toBeLessThan(array_search($courseWithFewLessons->id, $descIds));
        });

        it('fails if an invalid sort parameter is provided', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            getJson(route('teacher.courses.index', ['sort' => 'unsupported_field']))
                ->assertBadRequest();
        });

        it('returns empty data when no courses match the search', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            Course::factory()->for($author, 'author')->create();

            getJson(route('teacher.courses.index', ['filter[search]' => 'non-existent-course-title']))
                ->assertOk()
                ->assertJsonCount(0, 'data');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | pagination
    |--------------------------------------------------------------------------
    */
    describe('pagination', function () {
        it('returns a paginated list of course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            Course::factory()->count(3)->for($author, 'author')->create();

            getJson(route('teacher.courses.index'))
                ->assertOk()
                ->assertJsonStructure(paginationJsonStructure());
        });
    });
})->group('course', 'teacher');
