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

describe('Teacher -> CourseController -> destroy', function () {
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

            deleteJson(route('teacher.courses.destroy', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to delete the course', function () {
            $course = Course::factory()->create();

            deleteJson(route('teacher.courses.destroy', $course))
                ->assertUnauthorized();
            $this->assertDatabaseHas('courses', [
                'id' => $course->id,
            ]);
        });

        it('fails if a user without permissions tries to delete someone else\'s course', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();

            deleteJson(route('teacher.courses.destroy', $course))
                ->assertForbidden();
            $this->assertDatabaseHas('courses', [
                'id' => $course->id,
            ]);
        })->with([
            'user' => fn() => User::factory()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
        ]);

        it('allows a user to delete their own course', function ($userClosure, $courseClosure) {
            $user = $userClosure();

            Sanctum::actingAs($user);

            $filename = 'test-image.jpg';
            $courseInnerClosure = $courseClosure();
            $course = $courseInnerClosure($user, $filename);
            $lessons = Lesson::factory()->for($course, 'course')->count(8)->create();
            Storage::disk('courses')->put($filename, 'fake');

            deleteJson(route('teacher.courses.destroy', $course))
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
            'teacher' => fn() => User::factory()->teacher()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published' => fn() => fn($author, $filename) => Course::factory()->withImage($filename)->for($author, 'author')->create(),
            'unpublished' => fn() => fn($author, $filename) => Course::factory()->unpublished()->withImage($filename)->for($author, 'author')->create(),
            'banned' => fn() => fn($author, $filename) => Course::factory()->banned()->withImage($filename)->for($author, 'author')->create(),
        ]);

        it('fails if a banned user tries to delete their own course', function () {
            $bannedAuthor = User::factory()->teacher()->banned()->create();
            $course = Course::factory()->for($bannedAuthor, 'author')->create();

            Sanctum::actingAs($bannedAuthor);

            deleteJson(route('teacher.courses.destroy', $course))
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
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();

            $page = 1;
            $cacheKey = "courses:page:{$page}";
            $tags = [config('cache.tags.course_list')];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.course'));
            expect(Cache::tags($tags)->has($cacheKey))->toBeTrue();

            deleteJson(route('teacher.courses.destroy', $course))
                ->assertNoContent();

            expect(Cache::tags($tags)->has($cacheKey))->toBeFalse();
        });
    });
})->group('course', 'teacher');
