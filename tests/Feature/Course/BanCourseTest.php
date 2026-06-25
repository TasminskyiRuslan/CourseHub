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

describe('BanCourseController', function () {
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

            patchJson(route('course.ban', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to ban the course', function () {
            $course = Course::factory()->create();

            patchJson(route('course.ban', $course))
                ->assertUnauthorized();

            $course->refresh();
            expect($course->isBanned())->toBeFalse();
        });

        it('fails if users without permissions tries to ban someone else\'s course', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create();

            patchJson(route('course.ban', $course))
                ->assertForbidden();

            $course->refresh();
            expect($course->isBanned())->toBeFalse();
        })->with([
            'student' => fn() => User::factory()->student()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('fails if admin tries to ban their own course', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->for($admin, 'author')->create();

            patchJson(route('course.ban', $course))
                ->assertForbidden();

            $course->refresh();
            expect($course->isBanned())->toBeFalse();
        });

        it('allows users with permissions to ban any course', function ($user) {
            $this->withoutExceptionHandling();
            Notification::fake();

            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            patchJson(route('course.ban', $course))
                ->assertNoContent();

            $course->refresh();
            expect($course->isBanned())->toBeTrue();

            Notification::assertSentTo($course->author, CourseBannedNotification::class);
        })->with([
            'admin'       => fn() => User::factory()->admin()->create(),
            'super admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);

        it('does not send a notification if the course is already banned', function () {
            Notification::fake();

            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            $course = Course::factory()->banned()->create();

            patchJson(route('course.ban', $course))
                ->assertNoContent();

            Notification::assertNothingSent();
        });
    });
})->group('course');
