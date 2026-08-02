<?php

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('Admin -> CourseController -> destroy', function () {
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
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            deleteJson(route('admin.courses.destroy', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to delete a course', function () {
            $course = Course::factory()->create();

            deleteJson(route('admin.courses.destroy', $course))
                ->assertUnauthorized();
            $this->assertDatabaseHas('courses', [
                'id' => $course->id,
            ]);
        });

        it('fails if a user without permissions tries to delete a course', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            deleteJson(route('admin.courses.destroy', $course))
                ->assertForbidden();
            $this->assertDatabaseHas('courses', [
                'id' => $course->id,
            ]);
        })->with([
            'user' => fn() => User::factory()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows a user with permissions to delete a course', function ($userClosure, $courseClosure) {
            $user = $userClosure();

            Sanctum::actingAs($user);

            $filename = 'test-image.jpg';
            $courseInnerClosure = $courseClosure();
            $course = $courseInnerClosure($filename);
            $lessons = Lesson::factory()->for($course)->count(2)->create();
            Storage::disk('courses')->put($filename, 'fake');

            deleteJson(route('admin.courses.destroy', $course))
                ->assertNoContent();

            $this->assertSoftDeleted('courses', [
                'id' => $course->id,
            ]);

            foreach ($lessons as $lesson) {
                $this->assertSoftDeleted('lessons', ['id' => $lesson->id]);
                $lessonableTable = match ($course->type) {
                    CourseType::OFFLINE => 'offline_lessons',
                    CourseType::ONLINE => 'online_lessons',
                    CourseType::VIDEO => 'video_lessons',
                };
                $this->assertSoftDeleted($lessonableTable, [
                    'id' => $lesson->lessonable->id,
                ]);
            }

            Storage::disk('courses')->assertMissing($filename);
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published' => fn() => fn($filename) => Course::factory()->withImage($filename)->create(),
            'unpublished' => fn() => fn($filename) => Course::factory()->unpublished()->withImage($filename)->create(),
            'banned' => fn() => fn($filename) => Course::factory()->banned()->withImage($filename)->create(),
        ]);

        it('fails if a banned user tries to delete a course', function () {
            $bannedAdmin = User::factory()->admin()->banned()->create();
            $course = Course::factory()->create();

            Sanctum::actingAs($bannedAdmin);

            deleteJson(route('admin.courses.destroy', $course))
                ->assertForbidden();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the course cache when a course is deleted', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course = Course::factory()->create();

            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.course'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            deleteJson(route('admin.courses.destroy', $course))
                ->assertNoContent();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('course', 'admin');
