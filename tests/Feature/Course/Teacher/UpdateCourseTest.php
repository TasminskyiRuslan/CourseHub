<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Stripe\StripeClient;
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
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();

            patchJson(route('teacher.courses.update', $course), updatingCoursePayload([
                'title' => '',
                'slug' => '',
                'price' => ''
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'price']);
        });

        it('fails if the present fields are null', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();

            patchJson(route('teacher.courses.update', $course), updatingCoursePayload([
                'title' => null,
                'slug' => null,
                'price' => null
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'price']);
        });

        it('fails if the fields are too long', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();

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
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create(['slug' => 'my-slug']);
            $anotherCourse = Course::factory()->for($teacher, 'author')->create(['slug' => 'taken-slug']);

            patchJson(route('teacher.courses.update', $course), updatingCoursePayload([
                'slug' => $anotherCourse->slug,
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if the slug format is invalid', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();

            patchJson(route('teacher.courses.update', $course), updatingCoursePayload([
                'slug' => 'Invalid Slug!'
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('succeeds if the slug remains the same (ignore current)', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();

            $this->mock(StripeClient::class, function ($mock) {
                $mock->products = Mockery::mock();
                $mock->products->shouldReceive('update')->andReturn((object) ['id' => 'prod_mock_id']);
                $mock->prices = Mockery::mock();
                $mock->prices->shouldReceive('retrieve')->andReturn((object) [
                    'unit_amount' => 1000,
                    'currency' => config('cashier.currency'),
                ]);
                $mock->prices->shouldReceive('create')->andReturn((object) ['id' => 'price_mock_id']);
            });

            patchJson(route('teacher.courses.update', $course), [
                'slug' => $course->slug,
            ])
                ->assertOk()
                ->assertJsonFragment(['slug' => $course->slug])
                ->assertJsonStructure(['data' => teacherCourseJsonStructure()]);
        });

        it('fails if price is invalid', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();

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

        it('fails if a user without permissions tries to update someone else\'s course', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            patchJson(route('teacher.courses.update', $course), updatingCoursePayload())
                ->assertForbidden();
        })->with([
            'user' => fn() => User::factory()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('allows a user to update their own course', function ($userClosure, $courseClosure) {
            $user = $userClosure();
            Sanctum::actingAs($user);

            $courseInnerClosure = $courseClosure();
            $course = $courseInnerClosure($user);

            $this->mock(StripeClient::class, function ($mock) {
                $mock->products = Mockery::mock();
                $mock->products->shouldReceive('update')->andReturn((object) ['id' => 'prod_mock_id']);
                $mock->prices = Mockery::mock();
                $mock->prices->shouldReceive('retrieve')->andReturn((object) [
                    'unit_amount' => 1000,
                    'currency' => config('cashier.currency'),
                ]);
                $mock->prices->shouldReceive('create')->andReturn((object) ['id' => 'price_mock_id']);
            });

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
            'teacher' => fn() => User::factory()->teacher()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published' => fn() => fn($author) => Course::factory()->for($author, 'author')->create(),
            'unpublished' => fn() => fn($author) => Course::factory()->unpublished()->for($author, 'author')->create(),
            'banned' => fn() => fn($author) => Course::factory()->banned()->for($author, 'author')->create(),
        ]);

        it('fails if a banned user tries to update their own course', function () {
            $bannedUser = User::factory()->teacher()->banned()->create();
            $course = Course::factory()->for($bannedUser, 'author')->create();

            Sanctum::actingAs($bannedUser);

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
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $existingProductId = 'prod_' . Str::random(10);
            $existingPriceId = 'price_' . Str::random(10);

            $course = Course::factory()->for($teacher, 'author')->create([
                'title' => 'Old Course Title',
                'price' => '20.00',
                'stripe_product_id' => $existingProductId,
                'stripe_price_id' => $existingPriceId,
            ]);

            $this->mock(StripeClient::class, function ($mock) use ($existingProductId, $existingPriceId) {
                $mock->products = Mockery::mock();
                $mock->products->shouldReceive('update')
                    ->once()
                    ->with($existingProductId, ['name' => 'Updated Course Title'])
                    ->andReturn((object) ['id' => $existingProductId]);

                $mock->prices = Mockery::mock();
                $mock->prices->shouldReceive('retrieve')
                    ->once()
                    ->with($existingPriceId)
                    ->andReturn((object) [
                        'unit_amount' => 2000,
                        'currency' => config('cashier.currency'),
                    ]);

                $mock->prices->shouldNotReceive('create');
            });

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
        });

        it('creates a new stripe price when course price changes', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $existingProductId = 'prod_' . Str::random(10);
            $oldPriceId = 'price_' . Str::random(10);
            $expectedNewPriceId = 'price_' . Str::random(10);

            $course = Course::factory()->for($teacher, 'author')->create([
                'title' => 'Laravel Course',
                'price' => '20.00',
                'stripe_product_id' => $existingProductId,
                'stripe_price_id' => $oldPriceId,
            ]);

            $this->mock(StripeClient::class, function ($mock) use ($existingProductId, $oldPriceId, $expectedNewPriceId) {
                $mock->products = Mockery::mock();
                $mock->products->shouldReceive('update')
                    ->andReturn((object) ['id' => $existingProductId]);

                $mock->prices = Mockery::mock();
                $mock->prices->shouldReceive('retrieve')
                    ->once()
                    ->with($oldPriceId)
                    ->andReturn((object) [
                        'unit_amount' => 2000,
                        'currency' => config('cashier.currency'),
                    ]);

                $mock->prices->shouldReceive('create')
                    ->once()
                    ->andReturn((object) ['id' => $expectedNewPriceId]);
            });

            $payload = updatingCoursePayload([
                'title' => 'Laravel Course',
                'price' => '50.00',
            ]);

            patchJson(route('teacher.courses.update', $course), $payload)
                ->assertOk();

            $this->assertDatabaseHas('courses', [
                'id' => $course->id,
                'stripe_product_id' => $existingProductId,
                'stripe_price_id' => $expectedNewPriceId,
            ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the course cache when a course is updated', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();

            $this->mock(StripeClient::class, function ($mock) {
                $mock->products = Mockery::mock();
                $mock->products->shouldReceive('update')->andReturn((object) ['id' => 'prod_mock_id']);
                $mock->prices = Mockery::mock();
                $mock->prices->shouldReceive('retrieve')->andReturn((object) [
                    'unit_amount' => 1000,
                    'currency' => config('cashier.currency'),
                ]);
                $mock->prices->shouldReceive('create')->andReturn((object) ['id' => 'price_mock_id']);
            });

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
