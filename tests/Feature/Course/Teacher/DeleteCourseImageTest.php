<?php

declare(strict_types=1);

use App\Jobs\DeleteFileFromStorageJob;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('Teacher -> CourseImageController -> destroy', function () {
    beforeEach(function () {
        Cache::flush();
        Storage::fake('courses');
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

            deleteJson(route('teacher.courses.image.destroy', 'non-existing-slug'))
                ->assertNotFound();
        });

        it('fails if a non-author user tries to delete someone else\'s course image', function (?User $user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->withImage()->create();
            Storage::disk('courses')->put((string) $course->image_path, 'fake');

            deleteJson(route('teacher.courses.image.destroy', $course))
                ->assertNotFound();

            $course->refresh();
            expect($course->image_path)->not->toBeNull();
            Storage::disk('courses')->assertExists((string) $course->image_path);
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
        it('fails if an unauthenticated user tries to delete the course image', function () {
            $course = Course::factory()->withImage()->create();
            Storage::disk('courses')->put((string) $course->image_path, 'fake');

            deleteJson(route('teacher.courses.image.destroy', $course))
                ->assertUnauthorized();

            $course->refresh();
            expect($course->image_path)->not->toBeNull();
            Storage::disk('courses')->assertExists((string) $course->image_path);
        });

        it('allows a user to delete their own course image', function (?User $user, Factory $courseFactory) {
            Queue::fake();

            Sanctum::actingAs($user);

            $course = $courseFactory->for($user, 'author')->create();
            $imagePath = $course->image_path;

            deleteJson(route('teacher.courses.image.destroy', $course))
                ->assertNoContent();

            expect($course->refresh()->image_path)->toBeNull();

            Queue::assertPushed(DeleteFileFromStorageJob::class, function (DeleteFileFromStorageJob $job) use ($imagePath) {
                return $job->disk === 'courses' && $job->filePath === $imagePath;
            });
        })->with([
            'teacher' => fn () => User::factory()->teacher()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published course' => fn () => Course::factory()->withImage(),
            'unpublished course' => fn () => Course::factory()->unpublished()->withImage(),
            'banned course' => fn () => Course::factory()->banned()->withImage(),
        ]);

        it('fails if a banned user tries to delete their own course image', function () {
            $bannedAuthor = User::factory()->teacher()->banned()->create();
            $course = Course::factory()->for($bannedAuthor, 'author')->withImage()->create();
            Storage::disk('courses')->put((string) $course->image_path, 'fake');

            Sanctum::actingAs($bannedAuthor);

            deleteJson(route('teacher.courses.image.destroy', $course))
                ->assertForbidden();

            $course->refresh();
            expect($course->image_path)->not->toBeNull();
            Storage::disk('courses')->assertExists((string) $course->image_path);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('does not dispatch DeleteFileFromStorageJob when course has no image', function () {
            Queue::fake();

            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create([
                'image_path' => null,
            ]);

            deleteJson(route('teacher.courses.image.destroy', $course))
                ->assertNoContent();

            $course->refresh();
            expect($course->image_path)->toBeNull();

            Queue::assertNotPushed(DeleteFileFromStorageJob::class);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when the course image is deleted', function () {
            Queue::fake();

            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->withImage()->create();

            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.course'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            deleteJson(route('teacher.courses.image.destroy', $course))
                ->assertNoContent();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('course', 'teacher');
