<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('Teacher -> UnpublishCourseController', function () {
    beforeEach(function () {
        Cache::flush();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the course does not exist', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            patchJson(route('teacher.courses.unpublish', 'non-existing-slug'))
                ->assertNotFound();
        });

        it('fails if a non-author user tries to unpublish someone else\'s course', function (?User $user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            patchJson(route('teacher.courses.unpublish', $course))
                ->assertNotFound();

            $course->refresh();
            expect($course->isPublished())->toBeTrue();
        })->with([
            'user' => fn () => User::factory()->create(),
            'unverified teacher' => fn () => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn () => User::factory()->teacher()->create(),
            'admin' => fn () => User::factory()->admin()->create(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to unpublish the course', function () {
            $course = Course::factory()->create();

            patchJson(route('teacher.courses.unpublish', $course))
                ->assertUnauthorized();

            $course->refresh();
            expect($course->isPublished())->toBeTrue();
        });

        it('allows a user to unpublish their own course', function (?User $user, Factory $courseFactory) {
            Sanctum::actingAs($user);

            $course = $courseFactory->for($user, 'author')->create();

            patchJson(route('teacher.courses.unpublish', $course))
                ->assertOk()
                ->assertJsonStructure(['data' => teacherCourseJsonStructure()]);

            expect($course->refresh()->isPublished())->toBeFalse();
        })->with([
            'teacher' => fn () => User::factory()->teacher()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published course' => fn () => Course::factory(),
            'unpublished course' => fn () => Course::factory()->unpublished(),
            'banned course' => fn () => Course::factory()->banned(),
        ]);

        it('fails if a banned user tries to unpublish their own course', function () {
            $bannedAuthor = User::factory()->teacher()->banned()->create();
            $course = Course::factory()->for($bannedAuthor, 'author')->create();

            Sanctum::actingAs($bannedAuthor);

            patchJson(route('teacher.courses.unpublish', $course))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('resets published_at timestamp to null and returns loaded lessons_count', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            Lesson::factory()->for($course)->count(3)->create();

            expect($course->isPublished())->toBeTrue();

            $response = patchJson(route('teacher.courses.unpublish', $course))
                ->assertOk();

            $course->refresh();

            expect($course->isPublished())->toBeFalse()
                ->and($course->published_at)->toBeNull()
                ->and($response->json('data.lessons_count'))->toBe(3);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when the course is unpublished', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.course'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            patchJson(route('teacher.courses.unpublish', $course))
                ->assertOk();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('course', 'teacher');
