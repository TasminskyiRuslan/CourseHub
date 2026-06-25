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

describe('UnbanCourseController', function () {
    beforeEach(function () {
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
            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            patchJson(route('course.unban', 'non-existing-slug'))
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

            patchJson(route('course.unban', $course))
                ->assertUnauthorized();

            $course->refresh();
            expect($course->isBanned())->toBeTrue();
        });

        it('fails if users without permissions tries to unban someone else\'s course', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->banned()->create();

            patchJson(route('course.unban', $course))
                ->assertForbidden();

            $course->refresh();
            expect($course->isBanned())->toBeTrue();
        })->with([
            'student' => fn() => User::factory()->student()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('fails if admin tries to unban their own course', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->banned()->for($admin, 'author')->create();

            patchJson(route('course.unban', $course))
                ->assertForbidden();

            $course->refresh();
            expect($course->isBanned())->toBeTrue();
        });

        it('allows users with permissions to unban any course', function ($user) {
            $this->withoutExceptionHandling();
            Notification::fake();

            Sanctum::actingAs($user);

            $course = Course::factory()->banned()->create();

            patchJson(route('course.unban', $course))
                ->assertNoContent();

            $course->refresh();
            expect($course->isBanned())->toBeFalse();

            Notification::assertSentTo($course->author, CourseUnbannedNotification::class);
        })->with([
            'admin'       => fn() => User::factory()->admin()->create(),
            'super admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);

        it('does not send a notification if the course is already unbanned', function () {
            Notification::fake();

            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            $course = Course::factory()->create();

            patchJson(route('course.unban', $course))
                ->assertNoContent();

            Notification::assertNothingSent();
        });
    });
})->group('course');
