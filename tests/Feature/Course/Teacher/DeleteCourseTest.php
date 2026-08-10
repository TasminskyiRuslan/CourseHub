<?php

declare(strict_types=1);

use App\Jobs\Course\ArchiveCourseInStripeJob;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('Teacher -> CourseController -> destroy', function () {
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

            deleteJson(route('teacher.courses.destroy', 'non-existing-slug'))
                ->assertNotFound();
        });

        it('fails if a non-author user tries to delete someone else\'s course', function (?User $user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            deleteJson(route('teacher.courses.destroy', $course))
                ->assertNotFound();

            $this->assertNotSoftDeleted($course);
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
        it('fails if an unauthenticated user tries to delete the course', function () {
            $course = Course::factory()->create();

            deleteJson(route('teacher.courses.destroy', $course))
                ->assertUnauthorized();

            $this->assertNotSoftDeleted($course);
        });

        it('allows a user to delete their own course', function (?User $user, Factory $courseFactory) {
            Queue::fake();

            Sanctum::actingAs($user);

            $course = $courseFactory
                ->for($user, 'author')
                ->has(Lesson::factory()->count(8))
                ->create();

            deleteJson(route('teacher.courses.destroy', $course))
                ->assertNoContent();

            $this->assertSoftDeleted($course);

            $course->lessons->each(function (Lesson $lesson) {
                $this->assertSoftDeleted($lesson);
                $this->assertSoftDeleted($lesson->lessonable);
            });
        })->with([
            'teacher' => fn () => User::factory()->teacher()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published course' => fn () => Course::factory()->withImage('test-image.jpg'),
            'unpublished course' => fn () => Course::factory()->unpublished()->withImage('test-image.jpg'),
            'banned course' => fn () => Course::factory()->banned()->withImage('test-image.jpg'),
        ]);

        it('fails if a banned user tries to delete their own course', function () {
            $bannedAuthor = User::factory()->teacher()->banned()->create();
            $course = Course::factory()->for($bannedAuthor, 'author')->create();

            Sanctum::actingAs($bannedAuthor);

            deleteJson(route('teacher.courses.destroy', $course))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('dispatches ArchiveCourseInStripeJob when course has stripe_product_id', function () {
            Queue::fake();

            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $stripeProductId = 'prod_test_123456';
            $course = Course::factory()->for($author, 'author')->create([
                'stripe_product_id' => $stripeProductId,
            ]);

            deleteJson(route('teacher.courses.destroy', $course))
                ->assertNoContent();

            Queue::assertPushed(
                ArchiveCourseInStripeJob::class,
                fn (ArchiveCourseInStripeJob $job) => $job->stripeProductId === $stripeProductId
            );
        });

        it('does not dispatch ArchiveCourseInStripeJob when course lacks stripe_product_id', function () {
            Queue::fake();

            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create([
                'stripe_product_id' => null,
            ]);

            deleteJson(route('teacher.courses.destroy', $course))
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
            Queue::fake();

            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.course'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            deleteJson(route('teacher.courses.destroy', $course))
                ->assertNoContent();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('course', 'teacher');
