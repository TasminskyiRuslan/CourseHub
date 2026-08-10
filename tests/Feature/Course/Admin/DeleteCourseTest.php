<?php

declare(strict_types=1);

use App\Jobs\Course\ArchiveCourseInStripeJob;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('Admin -> CourseController -> destroy', function () {
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

            deleteJson(route('admin.courses.destroy', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to delete a course', function () {
            $course = Course::factory()->create();

            deleteJson(route('admin.courses.destroy', $course))
                ->assertUnauthorized();

            $this->assertNotSoftDeleted($course);
        });

        it('fails if a user without permissions tries to delete a course', function (?User $user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            deleteJson(route('admin.courses.destroy', $course))
                ->assertForbidden();

            $this->assertNotSoftDeleted($course);
        })->with([
            'user' => fn () => User::factory()->create(),
            'unverified teacher' => fn () => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn () => User::factory()->teacher()->create(),
        ]);

        it('fails if a banned user tries to delete a course', function () {
            $bannedAdmin = User::factory()->admin()->banned()->create();
            $course = Course::factory()->create();

            Sanctum::actingAs($bannedAdmin);

            deleteJson(route('admin.courses.destroy', $course))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('allows a user with permissions to delete a course and soft-deletes related lessons', function (User $user, Course $course) {
            Queue::fake();

            Sanctum::actingAs($user);

            $lessons = Lesson::factory()->for($course)->count(2)->create();

            deleteJson(route('admin.courses.destroy', $course))
                ->assertNoContent();

            $this->assertSoftDeleted($course);

            $lessons->each(function (Lesson $lesson) {
                $this->assertSoftDeleted($lesson);
                $this->assertSoftDeleted($lesson->lessonable);
            });
        })->with([
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published course' => fn () => Course::factory()->withImage('test-image.jpg')->create(),
            'unpublished course' => fn () => Course::factory()->unpublished()->withImage('test-image.jpg')->create(),
            'banned course' => fn () => Course::factory()->banned()->withImage('test-image.jpg')->create(),
        ]);

        it('dispatches ArchiveCourseInStripeJob when stripe_product_id is present', function () {
            Queue::fake();

            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $stripeProductId = 'prod_123456789';
            $course = Course::factory()->create(['stripe_product_id' => $stripeProductId]);

            deleteJson(route('admin.courses.destroy', $course))
                ->assertNoContent();

            Queue::assertPushed(
                ArchiveCourseInStripeJob::class,
                fn (ArchiveCourseInStripeJob $job) => $job->stripeProductId === $stripeProductId
            );
        });

        it('does not dispatch ArchiveCourseInStripeJob when stripe_product_id is missing', function () {
            Queue::fake();

            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create(['stripe_product_id' => null]);

            deleteJson(route('admin.courses.destroy', $course))
                ->assertNoContent();

            Queue::assertNotPushed(ArchiveCourseInStripeJob::class);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the course cache when a course is deleted', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();

            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.course'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            deleteJson(route('admin.courses.destroy', $course))
                ->assertNoContent();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('course', 'admin');
