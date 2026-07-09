<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

            deleteJson(route('teacher.course.image.destroy', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to delete the course image', function () {
            $course = Course::factory()->withImage()->create();
            Storage::disk('courses')->put($course->image_path, 'fake');

            deleteJson(route('teacher.course.image.destroy', $course))
                ->assertUnauthorized();
            $course->refresh();
            expect($course->image_path)->not->toBeNull();
            Storage::disk('courses')->assertExists($course->image_path);
        });

        it('fails if users without permissions tries to delete someone else\'s course image', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->withImage()->create();
            Storage::disk('courses')->put($course->image_path, 'fake');

            deleteJson(route('teacher.course.image.destroy', $course))
                ->assertForbidden();

            $course->refresh();
            expect($course->image_path)->not->toBeNull();
            Storage::disk('courses')->assertExists($course->image_path);
        })->with([
            'user'            => fn() => User::factory()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher'    => fn() => User::factory()->teacher()->create(),
            'admin'              => fn() => User::factory()->admin()->create(),
        ]);

        it('allows users to delete their own course image', function ($userClosure, $courseClosure) {
            $user = $userClosure();
            Sanctum::actingAs($user);

            $courseInnerClosure = $courseClosure();
            $course = $courseInnerClosure($user);

            Storage::disk('courses')->put($course->image_path, 'fake');

            deleteJson(route('teacher.course.image.destroy', $course))
                ->assertNoContent();

            $course->refresh();
            expect($course->image_path)->toBeNull();
            Storage::disk('courses')->assertMissing($course->image_path);
        })->with([
            'teacher'     => fn() => User::factory()->teacher()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published'   => fn() => fn($author) => Course::factory()->for($author, 'author')->withImage()->create(),
            'unpublished' => fn() => fn($author) => Course::factory()->unpublished()->for($author, 'author')->withImage()->create(),
            'banned'      => fn() => fn($author) => Course::factory()->banned()->for($author, 'author')->withImage()->create(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when the course image is deleted', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->withImage()->create();

            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.course'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            deleteJson(route('teacher.course.image.destroy', $course))
                ->assertNoContent();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('course', 'teacher');
