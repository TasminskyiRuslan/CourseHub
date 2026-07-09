<?php

use App\Models\Course;
use App\Models\User;
use App\Notifications\Course\CourseUnbannedNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('Admin -> UnbanCourseController', function () {
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

            patchJson(route('admin.course.unban', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to unban the course', function () {
            $course = Course::factory()->banned()->create();

            patchJson(route('admin.course.unban', $course))
                ->assertUnauthorized();

            $course->refresh();
            expect($course->isBanned())->toBeTrue();
        });

        it('fails if users without permissions tries to unban the course', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->banned()->create();

            patchJson(route('admin.course.unban', $course))
                ->assertForbidden();

            $course->refresh();
            expect($course->isBanned())->toBeTrue();
        })->with([
            'user'            => fn() => User::factory()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher'    => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows users with permissions to unban the course', function ($user) {
            Notification::fake();

            Sanctum::actingAs($user);

            $course = Course::factory()->banned()->create();

            patchJson(route('admin.course.unban', $course))
                ->assertNoContent();

            $course->refresh();
            expect($course->isBanned())->toBeFalse();

            Notification::assertSentTo($course->author, CourseUnbannedNotification::class);
        })->with([
            'admin'       => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ]);

        it('does not send a notification if the course is already unbanned', function () {
            Notification::fake();

            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();

            patchJson(route('admin.course.unban', $course))
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
        it('flushes the course cache when a course is unbanned', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->banned()->create();

            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.course'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            patchJson(route('admin.course.unban', $course))
                ->assertNoContent();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('course', 'admin');
