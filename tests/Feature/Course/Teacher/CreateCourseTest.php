<?php

declare(strict_types=1);

use App\Enums\CourseType;
use App\Jobs\Course\SyncCourseWithStripeJob;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('Teacher -> CourseController -> store', function () {
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
        it('fails with 422 when required payload fields are missing', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            postJson(route('teacher.courses.store'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'type', 'price']);
        });

        it('fails with 422 when course type is invalid', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $payload = [
                'title' => 'Test Course',
                'description' => 'Course description',
                'type' => 'invalid-type',
                'price' => 1000,
            ];

            postJson(route('teacher.courses.store'), $payload)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['type']);
        });

        it('fails with 422 when price is negative or non-numeric', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $payload = [
                'title' => 'Test Course',
                'description' => 'Course description',
                'type' => CourseType::ONLINE->value,
                'price' => -500,
            ];

            postJson(route('teacher.courses.store'), $payload)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['price']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to create a course', function () {
            $payload = [
                'title' => 'Unauthenticated Course',
                'description' => 'Description',
                'type' => CourseType::ONLINE->value,
                'price' => 1500,
            ];

            postJson(route('teacher.courses.store'), $payload)
                ->assertUnauthorized();

            $this->assertDatabaseMissing('courses', [
                'title' => 'Unauthenticated Course',
            ]);
        });

        it('fails if a user without permissions tries to create a course', function (?User $user) {
            Sanctum::actingAs($user);

            $payload = [
                'title' => 'Forbidden Course',
                'description' => 'Description',
                'type' => CourseType::ONLINE->value,
                'price' => 1500,
            ];

            postJson(route('teacher.courses.store'), $payload)
                ->assertForbidden();

            $this->assertDatabaseMissing('courses', [
                'title' => 'Forbidden Course',
            ]);
        })->with([
            'user' => fn () => User::factory()->create(),
            'admin' => fn () => User::factory()->admin()->create(),
        ]);

        it('fails if a banned teacher tries to create a course', function () {
            $bannedTeacher = User::factory()->teacher()->banned()->create();
            Sanctum::actingAs($bannedTeacher);

            $payload = [
                'title' => 'Banned Teacher Course',
                'description' => 'Description',
                'type' => CourseType::ONLINE->value,
                'price' => 1500,
            ];

            postJson(route('teacher.courses.store'), $payload)
                ->assertForbidden();

            $this->assertDatabaseMissing('courses', [
                'title' => 'Banned Teacher Course',
            ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('successfully creates a course, attaches teacher as author and dispatches SyncCourseWithStripeJob', function (?User $user) {
            Queue::fake();

            Sanctum::actingAs($user);

            $payload = [
                'title' => 'Mastering Laravel Architecture',
                'description' => 'Deep dive into advanced Laravel patterns.',
                'type' => CourseType::VIDEO->value,
                'price' => '4900.00',
            ];

            $response = postJson(route('teacher.courses.store'), $payload)
                ->assertCreated()
                ->assertJsonStructure([
                    'data' => teacherCourseJsonStructure(),
                ])
                ->assertJsonPath('data.title', $payload['title'])
                ->assertJsonPath('data.type', $payload['type'])
                ->assertJsonPath('data.price', $payload['price'])
                ->assertJsonPath('data.lessons_count', 0);

            $createdCourseId = $response->json('data.id');

            $this->assertDatabaseHas('courses', [
                'id' => $createdCourseId,
                'author_id' => $user->id,
                'title' => $payload['title'],
                'type' => $payload['type'],
                'price' => $payload['price'],
            ]);

            Queue::assertPushed(
                SyncCourseWithStripeJob::class,
                fn (SyncCourseWithStripeJob $job) => $job->course->id === $createdCourseId
            );
        })->with([
            'teacher' => fn () => User::factory()->teacher()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ]);
    });
})->group('course', 'teacher');
