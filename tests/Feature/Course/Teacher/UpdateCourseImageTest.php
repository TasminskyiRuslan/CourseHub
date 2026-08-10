<?php

declare(strict_types=1);

use App\Jobs\DeleteFileFromStorageJob;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('Teacher -> CourseImageController -> update', function () {

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
        it('fails if the required fields are missing', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            postJson(route('teacher.courses.image.update', $course), ['_method' => 'PUT'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails if the image is not a file', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            postJson(route('teacher.courses.image.update', $course), imagePayload([
                'image' => 'not-a-file',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails if the file is not an image', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            postJson(route('teacher.courses.image.update', $course), imagePayload([
                'image' => UploadedFile::fake()->create('document.pdf'),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails if the image exceeds the 2048KB size limit', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            postJson(route('teacher.courses.image.update', $course), imagePayload([
                'image' => UploadedFile::fake()->create('author.jpg')->size(2049),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('succeeds if image uploads with all allowed extensions', function (string $ext) {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            postJson(route('teacher.courses.image.update', $course), imagePayload([
                'image' => UploadedFile::fake()->image("author.$ext"),
            ]))
                ->assertOk()
                ->assertJsonStructure(['data' => teacherCourseJsonStructure()]);

            $course->refresh();
            expect($course->image_path)->not->toBeNull();
            Storage::disk('courses')->assertExists($course->image_path);
        })->with(['jpg', 'jpeg', 'png']);

        it('fails if the course does not exist', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            postJson(route('teacher.courses.image.update', 'non-existing-slug'), imagePayload())
                ->assertNotFound();
        });

        it('fails if a non-author user tries to update someone else\'s course image', function (?User $user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            postJson(route('teacher.courses.image.update', $course), imagePayload())
                ->assertNotFound();

            $course->refresh();

            expect($course->image_path)->toBeNull();
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
        it('fails if an unauthenticated user tries to update the course image', function () {
            $course = Course::factory()->create();

            postJson(route('teacher.courses.image.update', $course), imagePayload())
                ->assertUnauthorized();

            $course->refresh();

            expect($course->image_path)->toBeNull();
        });

        it('allows a user to update their own course image', function (?User $user, Factory $courseFactory) {
            Storage::fake('courses');
            Queue::fake();

            Sanctum::actingAs($user);

            $course = $courseFactory->for($user, 'author')->create();

            postJson(route('teacher.courses.image.update', $course), imagePayload())
                ->assertOk()
                ->assertJsonStructure(['data' => teacherCourseJsonStructure()]);

            expect($course->refresh()->image_path)->not->toBeNull();
        })->with([
            'teacher' => fn () => User::factory()->teacher()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published course' => fn () => Course::factory(),
            'unpublished course' => fn () => Course::factory()->unpublished(),
            'banned course' => fn () => Course::factory()->banned(),
        ]);

        it('fails if a banned user tries to update their own course image', function () {
            $bannedAuthor = User::factory()->teacher()->banned()->create();
            $course = Course::factory()->for($bannedAuthor, 'author')->create();

            Sanctum::actingAs($bannedAuthor);

            postJson(route('teacher.courses.image.update', $course), imagePayload())
                ->assertForbidden();

            $course->refresh();

            expect($course->image_path)->toBeNull();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | operations
    |--------------------------------------------------------------------------
    */
    describe('operations', function () {
        it('dispatches DeleteFileFromStorageJob when old image exists', function () {
            Queue::fake();

            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $oldImagePath = 'old-course-image.jpg';
            $course = Course::factory()->for($author, 'author')->create([
                'image_path' => $oldImagePath,
            ]);

            postJson(route('teacher.courses.image.update', $course), imagePayload())
                ->assertOk();

            Queue::assertPushed(
                DeleteFileFromStorageJob::class,
                fn (DeleteFileFromStorageJob $job) => $job->disk === 'courses' && $job->filePath === $oldImagePath
            );
        });

        it('does not dispatch DeleteFileFromStorageJob when old image is missing', function () {
            Queue::fake();

            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create([
                'image_path' => null,
            ]);

            postJson(route('teacher.courses.image.update', $course), imagePayload())
                ->assertOk();

            Queue::assertNotPushed(DeleteFileFromStorageJob::class);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when an course image is updated', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.course'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            postJson(route('teacher.courses.image.update', $course), imagePayload())
                ->assertOk();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('course', 'teacher');
