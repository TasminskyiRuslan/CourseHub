<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('Admin -> LessonController -> destroy', function () {
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

            $lesson = Lesson::factory()->create();

            deleteJson(route('admin.courses.lessons.destroy', ['non-existing-slug', $lesson]))
                ->assertNotFound();
        });

        it('fails if the lesson does not exist', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();

            deleteJson(route('admin.courses.lessons.destroy', [$course, 'non-existing-slug']))
                ->assertNotFound();
        });

        it('fails if the lesson is from another course', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->create();

            deleteJson(route('admin.courses.lessons.destroy', [$course, $lesson]))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to delete the lesson', function () {
            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            deleteJson(route('admin.courses.lessons.destroy', [$course, $lesson]))
                ->assertUnauthorized();

            $this->assertNotSoftDeleted($lesson);
            $this->assertNotSoftDeleted($lesson->lessonable);
        });

        it('fails if a user without permissions tries to delete a lesson', function (?User $user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            deleteJson(route('admin.courses.lessons.destroy', [$course, $lesson]))
                ->assertForbidden();

            $this->assertNotSoftDeleted($lesson);
            $this->assertNotSoftDeleted($lesson->lessonable);
        })->with([
            'user' => fn () => User::factory()->create(),
            'teacher' => fn () => User::factory()->teacher()->create(),
            'unverified teacher' => fn () => User::factory()->teacher()->unverified()->create(),
        ]);

        it('allows a user with permission to delete a lesson', function (?User $user, Course $course) {
            Sanctum::actingAs($user);

            $lesson = Lesson::factory()->for($course, 'course')->create();

            deleteJson(route('admin.courses.lessons.destroy', [$course, $lesson]))
                ->assertNoContent();

            $this->assertSoftDeleted($lesson);
            $this->assertSoftDeleted($lesson->lessonable);
        })->with([
            'admin' => fn () => User::factory()->admin()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published course' => fn () => Course::factory()->create(),
            'unpublished course' => fn () => Course::factory()->unpublished()->create(),
            'banned course' => fn () => Course::factory()->banned()->create(),
        ]);

        it('fails if a banned user tries to delete a lesson', function () {
            $bannedAuthor = User::factory()->admin()->banned()->create();
            $course = Course::factory()->for($bannedAuthor, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            Sanctum::actingAs($bannedAuthor);

            deleteJson(route('admin.courses.lessons.destroy', [$course, $lesson]))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the lesson cache when a lesson is deleted', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.lesson'));

            expect(Cache::tags($tags)->get($cacheKey))->not->toBeNull();

            deleteJson(route('admin.courses.lessons.destroy', [$course, $lesson]))->assertNoContent();

            expect(Cache::tags($tags)->get($cacheKey))->toBeNull();
        });
    });
})->group('lesson', 'admin');
