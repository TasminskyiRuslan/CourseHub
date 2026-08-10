<?php

declare(strict_types=1);

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('Teacher -> LessonController -> destroy', function () {
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
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $lesson = Lesson::factory()->create();

            deleteJson(route('teacher.courses.lessons.destroy', ['non-existing-slug', $lesson]))
                ->assertNotFound();
        });

        it('fails if the lesson does not exist', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->create();

            deleteJson(route('teacher.courses.lessons.destroy', [$course, 'non-existing-slug']))
                ->assertNotFound();
        });

        it('fails if the lesson is from another course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->create();

            deleteJson(route('teacher.courses.lessons.destroy', [$course, $lesson]))
                ->assertNotFound();
        });

        it('fails if a non-author user tries to delete a lesson', function (?User $user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            deleteJson(route('teacher.courses.lessons.destroy', [$course, $lesson]))
                ->assertNotFound();

            $this->assertNotSoftDeleted($lesson);
            $this->assertNotSoftDeleted($lesson->lessonable);
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
        it('fails if an unauthenticated user tries to delete the lesson', function () {
            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            deleteJson(route('teacher.courses.lessons.destroy', [$course, $lesson]))
                ->assertUnauthorized();

            $lessonableTable = match ($course->type) {
                CourseType::OFFLINE => 'offline_lessons',
                CourseType::ONLINE => 'online_lessons',
                CourseType::VIDEO => 'video_lessons',
            };

            $this->assertNotSoftDeleted($lesson);
            $this->assertNotSoftDeleted($lesson->lessonable);
        });

        it('allows users with permission to delete a lesson', function (User $user, Factory $courseFactory) {
            Sanctum::actingAs($user);

            $course = $courseFactory->for($user, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            deleteJson(route('teacher.courses.lessons.destroy', [$course, $lesson]))
                ->assertNoContent();

            $this->assertSoftDeleted($lesson);
            $this->assertSoftDeleted($lesson->lessonable);
        })->with([
            'teacher' => fn () => User::factory()->teacher()->create(),
            'super-admin' => fn () => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published course' => fn () => Course::factory(),
            'unpublished course' => fn () => Course::factory()->unpublished(),
            'banned course' => fn () => Course::factory()->banned(),
        ]);

        it('fails if a banned user tries to delete a lesson', function () {
            $bannedAuthor = User::factory()->teacher()->banned()->create();
            $course = Course::factory()->for($bannedAuthor, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            Sanctum::actingAs($bannedAuthor);

            deleteJson(route('teacher.courses.lessons.destroy', [$course, $lesson]))
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
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.lesson'));
            expect(Cache::tags($tags)->get($cacheKey))->not->toBeNull();

            deleteJson(route('teacher.courses.lessons.destroy', [$course, $lesson]))
                ->assertNoContent();

            expect(Cache::tags($tags)->get($cacheKey))->toBeNull();
        });
    });
})->group('lesson', 'teacher');
