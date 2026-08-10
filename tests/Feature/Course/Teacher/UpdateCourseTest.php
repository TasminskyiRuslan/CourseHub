<?php

declare(strict_types=1);

use App\Jobs\Course\SyncCourseWithStripeJob;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('Teacher -> CourseController -> update', function () {
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
        it('fails if the present fields are empty', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            patchJson(route('teacher.courses.update', $course), updatingCoursePayload([
                'title' => '',
                'slug' => '',
                'price' => '',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'price']);
        });

        it('fails if the present fields are null', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            patchJson(route('teacher.courses.update', $course), updatingCoursePayload([
                'title' => null,
                'slug' => null,
                'price' => null,
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'price']);
        });

        it('fails if the fields are too long', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            patchJson(route('teacher.courses.update', $course), updatingCoursePayload([
                'title' => str_repeat('A', 256),
                'slug' => str_repeat('B', 256),
                'description' => str_repeat('C', 5001),
                'price' => 999999999.99,
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'description', 'price']);
        });

        it('fails if the slug is taken by another course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create(['slug' => 'my-slug']);
            $anotherCourse = Course::factory()->for($author, 'author')->create(['slug' => 'taken-slug']);

            patchJson(route('teacher.courses.update', $course), updatingCoursePayload([
                'slug' => $anotherCourse->slug,
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if the slug format is invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            patchJson(route('teacher.courses.update', $course), updatingCoursePayload([
                'slug' => 'Invalid Slug!',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('succeeds if the slug remains the same (ignore current)', function () {
            Queue::fake();

            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            patchJson(route('teacher.courses.update', $course), [
                'slug' => $course->slug,
            ])
                ->assertOk()
                ->assertJsonFragment(['slug' => $course->slug])
                ->assertJsonStructure(['data' => teacherCourseJsonStructure()]);
        });

        it('fails if price is invalid', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            patchJson(route('teacher.courses.update', $course), ['price' => 'not-a-number'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['price']);
        });

        it('fails if the course does not exist', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            patchJson(route('teacher.courses.update', 'non-existing-slug'))
                ->assertNotFound();
        });

        it('fails if a non-author user tries to update someone else\'s course', function (?User $user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            patchJson(route('teacher.courses.update', $course), updatingCoursePayload())
                ->assertNotFound();
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
        it('fails if an unauthenticated user tries to update a course', function () {
            $course = Course::factory()->create();

            patchJson(route('teacher.courses.update', $course), updatingCoursePayload())
                ->assertUnauthorized();
        });

        it('allows a user to update their own course', function (?User $user, Factory $courseFactory) {
            Queue::fake();

            Sanctum::actingAs($user);

            $course = $courseFactory->for($user, 'author')->create();

            $data = updatingCoursePayload();

            patchJson(route('teacher.courses.update', $course), $data)
                ->assertOk()
                ->assertJsonPath('data.title', $data['title'])
                ->assertJsonStructure(['data' => teacherCourseJsonStructure()]);

            $this->assertDatabaseHas('courses', [
                'id' => $course->id,
                'title' => $data['title'],
                'description' => $data['description'],
            ]);
        })->with([
            'teacher' => fn () => User::factory()->teacher()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published course' => fn () => Course::factory(),
            'unpublished course' => fn () => Course::factory()->unpublished(),
            'banned course' => fn () => Course::factory()->banned(),
        ]);

        it('fails if a banned user tries to update their own course', function () {
            $bannedAuthor = User::factory()->teacher()->banned()->create();
            $course = Course::factory()->for($bannedAuthor, 'author')->create();

            Sanctum::actingAs($bannedAuthor);

            patchJson(route('teacher.courses.update', $course), updatingCoursePayload())
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('updates stripe product title if course title changed', function () {
            Queue::fake();

            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $existingProductId = 'prod_'.Str::random(10);
            $existingPriceId = 'price_'.Str::random(10);

            $course = Course::factory()->for($author, 'author')->create([
                'title' => 'Old Course Title',
                'price' => '20.00',
                'stripe_product_id' => $existingProductId,
                'stripe_price_id' => $existingPriceId,
            ]);

            $payload = updatingCoursePayload([
                'title' => 'Updated Course Title',
                'price' => '20.00',
            ]);

            patchJson(route('teacher.courses.update', $course), $payload)
                ->assertOk();

            $this->assertDatabaseHas('courses', [
                'id' => $course->id,
                'title' => $payload['title'],
                'stripe_product_id' => $existingProductId,
                'stripe_price_id' => $existingPriceId,
            ]);

            Queue::assertPushed(SyncCourseWithStripeJob::class, function ($job) use ($course) {
                return $job->course->id === $course->id;
            });
        });

        it('creates a new stripe price when course price changes', function () {
            Queue::fake();

            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $existingProductId = 'prod_'.Str::random(10);
            $oldPriceId = 'price_'.Str::random(10);

            $course = Course::factory()->for($author, 'author')->create([
                'title' => 'Laravel Course',
                'price' => '20.00',
                'stripe_product_id' => $existingProductId,
                'stripe_price_id' => $oldPriceId,
            ]);

            $payload = updatingCoursePayload([
                'title' => 'Laravel Course',
                'price' => '50.00',
            ]);

            patchJson(route('teacher.courses.update', $course), $payload)
                ->assertOk();

            Queue::assertPushed(SyncCourseWithStripeJob::class, function ($job) use ($course) {
                return $job->course->id === $course->id;
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the course cache when a course is updated', function () {
            Queue::fake();

            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.course'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            patchJson(route('teacher.courses.update', $course), updatingCoursePayload())
                ->assertOk();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('course', 'teacher');
