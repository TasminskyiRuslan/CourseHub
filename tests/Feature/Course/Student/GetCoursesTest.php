<?php

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

describe('Student -> CourseController -> index', function () {
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
            getJson(route('student.courses.index'))
                ->assertUnauthorized();
        });

        it('allows a student to retrieve their enrolled courses', function ($user) {
            Sanctum::actingAs($user);

            $author = User::factory()->teacher()->create();
            $enrolledCourses = Course::factory()->count(3)->for($author, 'author')->create();
            $otherCourses = Course::factory()->count(2)->for($author, 'author')->create();

            $user->enrolledCourses()->attach($enrolledCourses, [], 'enrolledCourses');

            $response = getJson(route('student.courses.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => studentCourseJsonStructure()
                    ]
                ]);

            $responseDataIds = collect($response->json('data'))->pluck('id');
            foreach ($enrolledCourses as $course) {
                expect($responseDataIds)->toContain($course->id);
            }

            foreach ($otherCourses as $course) {
                expect($responseDataIds)->not->toContain($course->id);
            }
        })->with([
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ]);

        it('fails if a banned user tries to retrieve their enrolled courses', function () {
            $bannedUser = User::factory()->banned()->create();
            $author = User::factory()->teacher()->create();
            $courses = Course::factory()->count(3)->for($author, 'author')->create();

            $bannedUser->enrolledCourses()->attach($courses, [], 'enrolledCourses');

            Sanctum::actingAs($bannedUser);

            getJson(route('student.courses.index'))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | filters & sorting
    |--------------------------------------------------------------------------
    */
    describe('filters & sorting', function () {
        it('filters enrolled courses by a search string', function () {
            $user = User::factory()->create();
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($user);

            $course1 = Course::factory()->for($author, 'author')->create(['title' => 'Laravel Deep Dive']);
            $course2 = Course::factory()->for($author, 'author')->create(['title' => 'React Basics']);

            $user->enrolledCourses()->attach([$course1->id, $course2->id], [], 'enrolledCourses');
            $searchString = substr($course1->title, 8);

            getJson(route('student.courses.index', ['filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonFragment(['id' => $course1->id])
                ->assertJsonMissing(['data' => [['id' => $course2->id]]])
                ->assertJsonStructure([
                    'data' => [
                        '*' => studentCourseJsonStructure()
                    ]
                ]);
        });

        it('filters enrolled courses by type', function () {
            $user = User::factory()->create();
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($user);

            $onlineCourse = Course::factory()->type(CourseType::ONLINE)->for($author, 'author')->create();
            $videoCourse = Course::factory()->type(CourseType::VIDEO)->for($author, 'author')->create();

            $user->enrolledCourses()->attach([$onlineCourse->id, $videoCourse->id], [], 'enrolledCourses');

            getJson(route('student.courses.index', ['filter[type]' => CourseType::ONLINE->value]))
                ->assertOk()
                ->assertJsonFragment(['id' => $onlineCourse->id])
                ->assertJsonMissing(['data' => [['id' => $videoCourse->id]]]);
        });

        it('filters enrolled courses by author slug', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $authorA = User::factory()->teacher()->create();
            $authorB = User::factory()->teacher()->create();

            $courseA = Course::factory()->for($authorA, 'author')->create();
            $courseB = Course::factory()->for($authorB, 'author')->create();

            $user->enrolledCourses()->attach([$courseA->id, $courseB->id], [], 'enrolledCourses');

            getJson(route('student.courses.index', ['filter[author]' => $authorA->slug]))
                ->assertOk()
                ->assertJsonFragment(['id' => $courseA->id])
                ->assertJsonMissing(['data' => [['id' => $courseB->id]]]);
        });

        it('sorts enrolled courses by enrolled_at (desc) by default', function () {
            $user = User::factory()->create();
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($user);

            $oldCourse = Course::factory()->for($author, 'author')->create();
            $newCourse = Course::factory()->for($author, 'author')->create();

            $user->enrolledCourses()->attach($oldCourse, ['enrolled_at' => now()->subDays(2)], 'enrolledCourses');
            $user->enrolledCourses()->attach($newCourse, ['enrolled_at' => now()->subDay()], 'enrolledCourses');

            $response = getJson(route('student.courses.index'))->assertOk();
            $ids = collect($response->json('data'))->pluck('id')->all();

            expect(array_search($newCourse->id, $ids))->toBeLessThan(array_search($oldCourse->id, $ids));
        });

        it('sorts enrolled courses by enrolled_at (asc and desc)', function () {
            $user = User::factory()->create();
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($user);

            $oldCourse = Course::factory()->for($author, 'author')->create();
            $newCourse = Course::factory()->for($author, 'author')->create();

            $user->enrolledCourses()->attach($oldCourse, ['enrolled_at' => now()->subDays(2)], 'enrolledCourses');
            $user->enrolledCourses()->attach($newCourse, ['enrolled_at' => now()->subDay()], 'enrolledCourses');

            $ascResponse = getJson(route('student.courses.index', ['sort' => 'enrolled_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldCourse->id, $ascIds))->toBeLessThan(array_search($newCourse->id, $ascIds));

            $descResponse = getJson(route('student.courses.index', ['sort' => '-enrolled_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newCourse->id, $descIds))->toBeLessThan(array_search($oldCourse->id, $descIds));
        });

        it('sorts enrolled courses by published_at (asc and desc)', function () {
            $user = User::factory()->create();
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($user);

            $oldCourse = Course::factory()->for($author, 'author')->create(['published_at' => now()->subDays(3)]);
            $newCourse = Course::factory()->for($author, 'author')->create(['published_at' => now()->subDay()]);

            $user->enrolledCourses()->attach([$oldCourse->id, $newCourse->id], [], 'enrolledCourses');

            $ascResponse = getJson(route('student.courses.index', ['sort' => 'published_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldCourse->id, $ascIds))->toBeLessThan(array_search($newCourse->id, $ascIds));

            $descResponse = getJson(route('student.courses.index', ['sort' => '-published_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newCourse->id, $descIds))->toBeLessThan(array_search($oldCourse->id, $descIds));
        });

        it('sorts enrolled courses by title (asc and desc)', function () {
            $user = User::factory()->create();
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($user);

            $courseA = Course::factory()->for($author, 'author')->create(['title' => 'Alpha Course']);
            $courseB = Course::factory()->for($author, 'author')->create(['title' => 'Beta Course']);

            $user->enrolledCourses()->attach([$courseA->id, $courseB->id], [], 'enrolledCourses');

            $ascResponse = getJson(route('student.courses.index', ['sort' => 'title']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($courseA->id, $ascIds))->toBeLessThan(array_search($courseB->id, $ascIds));

            $descResponse = getJson(route('student.courses.index', ['sort' => '-title']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($courseB->id, $descIds))->toBeLessThan(array_search($courseA->id, $descIds));
        });

        it('sorts enrolled courses by price (asc and desc)', function () {
            $user = User::factory()->create();
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($user);

            $cheap = Course::factory()->for($author, 'author')->create(['price' => 150]);
            $expensive = Course::factory()->for($author, 'author')->create(['price' => 450]);

            $user->enrolledCourses()->attach([$cheap->id, $expensive->id], [], 'enrolledCourses');

            $ascResponse = getJson(route('student.courses.index', ['sort' => 'price']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($cheap->id, $ascIds))->toBeLessThan(array_search($expensive->id, $ascIds));

            $descResponse = getJson(route('student.courses.index', ['sort' => '-price']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($expensive->id, $descIds))->toBeLessThan(array_search($cheap->id, $descIds));
        });

        it('returns empty data when no enrolled courses match the search', function () {
            $user = User::factory()->create();
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($user);

            $course = Course::factory()->for($author, 'author')->create();
            $user->enrolledCourses()->attach($course, [], 'enrolledCourses');

            getJson(route('student.courses.index', ['filter[search]' => 'non-existent-course-title']))
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
        it('returns a paginated list of student courses', function () {
            $user = User::factory()->create();
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($user);

            $courses = Course::factory()->count(3)->for($author, 'author')->create();
            $user->enrolledCourses()->attach($courses, [], 'enrolledCourses');

            getJson(route('student.courses.index'))
                ->assertOk()
                ->assertJsonStructure(paginationJsonStructure());
        });
    });
})->group('course', 'student');
