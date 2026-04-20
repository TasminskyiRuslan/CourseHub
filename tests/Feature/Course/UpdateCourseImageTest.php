<?php

use App\Enums\CourseType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use App\Models\Course;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('CourseImageController -> update', function () {

    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
        Storage::fake('courses');
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the required fields are missing', function () {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            Sanctum::actingAs($author);

            postJson(route('course.image.update', $course), ['_method' => 'PUT'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails if the image is not a file', function () {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            Sanctum::actingAs($author);

            postJson(route('course.image.update', $course), imagePayload([
                'image' => 'not-a-file',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails if the file is not an image', function () {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            Sanctum::actingAs($author);

            postJson(route('course.image.update', $course), imagePayload([
                'image' => UploadedFile::fake()->create('document.pdf'),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails if the image exceeds the 2048KB size limit', function () {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            Sanctum::actingAs($author);

            postJson(route('course.image.update', $course), imagePayload([
                'image' => UploadedFile::fake()->create('author.jpg')->size(2049),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('succeeds if image uploads with all allowed extensions', function (string $ext) {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            Sanctum::actingAs($author);

            postJson(route('course.image.update', $course), imagePayload([
                'image' => UploadedFile::fake()->image("author.$ext"),
            ]))
                ->assertOk()
                ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true, withLessons: true, courseType: $course->type)]);
            $course->refresh();
            expect($course->image_path)->not->toBeNull();
            Storage::disk('courses')->assertExists($course->image_path);
        })->with(['jpg', 'jpeg', 'png']);

        it('fails if the course does not exist', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            postJson(route('course.image.update', 'non-existing-slug'), imagePayload())
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to update the course image', function () {
            $course = Course::factory()->create();

            postJson(route('course.image.update', $course), imagePayload())
                ->assertUnauthorized();

            $course->refresh();
            expect($course->image_path)->toBeNull();
            Storage::disk('courses')->assertMissing($course->image_path);
        });

        it('fails if users tries to update someone else\'s course image', function ($user) {
            $course = Course::factory()->create();

            if ($user) {
                Sanctum::actingAs($user);
            }

            postJson(route('course.image.update', $course), imagePayload())
                ->assertForbidden();

            $course->refresh();
            expect($course->image_path)->toBeNull();
            Storage::disk('courses')->assertMissing($course->image_path);
        })->with([
            'student' => fn() => User::factory()->student()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('allows author to update their own course image', function () {
            $author = User::factory()->teacher()->create();
            $course = Course::factory()->for($author, 'author')->create();
            Sanctum::actingAs($author);

            $data = imagePayload();

            postJson(route('course.image.update', $course), $data)
                ->assertOk()
                ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true, withLessons: true, courseType: $course->type)]);
            $course->refresh();
            expect($course->image_path)->not()->toBeNull();
            Storage::disk('courses')->assertExists($course->image_path);
        });

        it('allows super-admin to update any course image', function () {
            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            $course = Course::factory()->create();
            Sanctum::actingAs($superAdmin);

            $data = imagePayload();

            postJson(route('course.image.update', $course), $data)
                ->assertOk()
                ->assertJsonStructure(['data' => courseJsonStructure(withAuthor: true, withLessonsCount: true, withLessons: true, courseType: $course->type)]);
            $course->refresh();
            expect($course->image_path)->not()->toBeNull();
            Storage::disk('courses')->assertExists($course->image_path);
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
            $course = Course::factory()->for($author, 'author')->create();
            Sanctum::actingAs($author);

            Cache::tags([config('cache.tags.course')])->put('courses', 'test_value', config('cache.ttl.books'));

            postJson(route('course.image.update', $course), imagePayload())
                ->assertOk();
            expect(Cache::tags([config('cache.tags.course')])->get('courses'))->toBeNull();
        });
    });
})->group('course');
