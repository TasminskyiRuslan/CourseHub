<?php

declare(strict_types=1);

use App\Enums\CheckoutStatus;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Cashier\Checkout;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('Student -> CheckoutController', function () {
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
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            postJson(route('student.courses.checkout', 'non-existing-slug'))
                ->assertNotFound();
        });

        it('fails if the author tries to purchase their own course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            postJson(route('student.courses.checkout', $course))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to checkout', function () {
            $course = Course::factory()->create();

            postJson(route('student.courses.checkout', $course))
                ->assertUnauthorized();
        });

        it('fails if a banned user tries to checkout', function () {
            $bannedUser = User::factory()->banned()->create();
            $course = Course::factory()->create();

            Sanctum::actingAs($bannedUser);

            postJson(route('student.courses.checkout', $course))
                ->assertForbidden();

            $this->assertDatabaseMissing('course_user', [
                'user_id' => $bannedUser->id,
                'course_id' => $course->id,
            ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('returns enrolled status if user is already enrolled', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $course = Course::factory()->create();
            $user->enrolledCourses()->attach($course->id);

            postJson(route('student.courses.checkout', $course))
                ->assertOk()
                ->assertJson([
                    'data' => [
                        'status' => CheckoutStatus::ENROLLED->value,
                        'is_enrolled' => true,
                        'course_id' => $course->id,
                        'checkout_url' => null,
                    ],
                ]);

            $this->assertDatabaseHas('course_user', [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ]);
        });

        it('instantly enrolls user if the course is free', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $freeCourse = Course::factory()->free()->create();

            postJson(route('student.courses.checkout', $freeCourse->slug))
                ->assertOk()
                ->assertJson([
                    'data' => [
                        'status' => CheckoutStatus::ENROLLED->value,
                        'is_enrolled' => true,
                        'course_id' => $freeCourse->id,
                        'checkout_url' => null,
                    ],
                ]);

            expect($user->fresh()->isEnrolledIn($freeCourse))->toBeTrue();

            $this->assertDatabaseHas('course_user', [
                'user_id' => $user->id,
                'course_id' => $freeCourse->id,
            ]);
        });

        it('generates a stripe checkout url with correct metadata for paid courses', function () {
            /** @var User|MockInterface $user */
            $user = Mockery::mock(User::factory()->create())->makePartial();
            Sanctum::actingAs($user);

            $course = Course::factory()->create([
                'price' => 2900,
                'stripe_price_id' => 'price_123456789',
            ]);

            /** @var Checkout|MockInterface $mockCheckout */
            $mockCheckout = Mockery::mock(Checkout::class);
            $mockCheckout->url = 'https://checkout.stripe.com/c/pay/cs_test_123';

            $baseUrl = rtrim(config('app.frontend_url'), '/');

            $user->shouldReceive('checkout')
                ->once()
                ->with(
                    [$course->stripe_price_id => 1],
                    [
                        'success_url' => "{$baseUrl}/courses/{$course->slug}?status=success",
                        'cancel_url' => "{$baseUrl}/courses/{$course->slug}?status=cancelled",
                        'metadata' => [
                            'user_id' => (string) $user->id,
                            'course_id' => (string) $course->id,
                        ],
                    ]
                )
                ->andReturn($mockCheckout);

            postJson(route('student.courses.checkout', $course))
                ->assertOk()
                ->assertJson([
                    'data' => [
                        'status' => CheckoutStatus::PAYMENT_REQUIRED->value,
                        'is_enrolled' => false,
                        'course_id' => $course->id,
                        'checkout_url' => 'https://checkout.stripe.com/c/pay/cs_test_123',
                    ],
                ]);

            $this->assertDatabaseMissing('course_user', [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ]);
        });
    });
})->group('course', 'student');
