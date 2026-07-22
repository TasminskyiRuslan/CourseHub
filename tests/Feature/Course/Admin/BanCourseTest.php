<?php

use App\Models\Course;
use App\Models\User;
use App\Notifications\Course\CourseBannedNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('Admin -> BanCourseController', function () {
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
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            patchJson(route('admin.courses.ban', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to ban a course', function () {
            $course = Course::factory()->create();

            patchJson(route('admin.courses.ban', $course))
                ->assertUnauthorized();

            $course->refresh();
            expect($course->isBanned())->toBeFalse();
        });

        it('fails if a user without permissions tries to ban a course', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            patchJson(route('admin.courses.ban', $course))
                ->assertForbidden();

            $course->refresh();
            expect($course->isBanned())->toBeFalse();
        })->with([
            'user' => fn() => User::factory()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows a user with permissions to ban a course', function ($userClosure, $courseClosure) {
            Notification::fake();
            Sanctum::actingAs($userClosure());

            $courseInnerClosure = $courseClosure();
            $course = $courseInnerClosure();

            patchJson(route('admin.courses.ban', $course))
                ->assertNoContent();

            $course->refresh();
            expect($course->isBanned())->toBeTrue();

            Notification::assertSentTo($course->author, CourseBannedNotification::class);
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published' => fn() => fn() => Course::factory()->create(),
            'unpublished' => fn() => fn() => Course::factory()->unpublished()->create(),
        ]);

        it('does not send a notification if the course is already banned', function () {
            Notification::fake();

            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->banned()->create();

            patchJson(route('admin.courses.ban', $course))
                ->assertNoContent();

            Notification::assertNothingSent();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the course cache when a course is banned', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();

            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.course'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            patchJson(route('admin.courses.ban', $course))
                ->assertNoContent();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('course', 'admin');
