<?php

declare(strict_types=1);

namespace Tests\Feature\Listeners;

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Cashier\Events\WebhookReceived;

uses(RefreshDatabase::class);

describe('StripeCheckoutCompletedListener', function () {
    beforeEach(function () {
        Cache::flush();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
    });

    it('enrolls user to the course when checkout.session.completed event is dispatched', function () {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $payload = stripeCheckoutWebhookPayload([
            'data' => [
                'object' => [
                    'id' => 'cs_test_fake_123',
                    'metadata' => [
                        'user_id' => (string) $user->id,
                        'course_id' => (string) $course->id,
                    ],
                ],
            ],
        ]);

        WebhookReceived::dispatch($payload);

        $this->assertDatabaseHas('course_user', [
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        expect($user->fresh()->isEnrolledIn($course))->toBeTrue();
    });

    it('does not enroll user when receiving unrelated stripe event', function () {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $payload = stripeCheckoutWebhookPayload([
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'metadata' => [
                        'user_id' => (string) $user->id,
                        'course_id' => (string) $course->id,
                    ],
                ],
            ],
        ]);

        WebhookReceived::dispatch($payload);

        $this->assertDatabaseMissing('course_user', [
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);
    });

    it('handles missing metadata gracefully without throwing errors', function () {
        $payload = stripeCheckoutWebhookPayload();

        WebhookReceived::dispatch($payload);

        $this->assertDatabaseCount('course_user', 0);
    });

    it('does not crash if user or course from metadata is not found in database', function () {
        $course = Course::factory()->create();

        $payload = stripeCheckoutWebhookPayload([
            'data' => [
                'object' => [
                    'id' => 'cs_test_fake_999',
                    'metadata' => [
                        'user_id' => '999999',
                        'course_id' => (string) $course->id,
                    ],
                ],
            ],
        ]);

        WebhookReceived::dispatch($payload);

        $this->assertDatabaseCount('course_user', 0);
    });
})->group('course', 'student');
