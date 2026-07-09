<?php

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

            deleteJson(route('teacher.course.lesson.destroy', ['non-existing-slug', $lesson]))
                ->assertNotFound();
        });

        it('fails if the lesson does not exist', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->create();

            deleteJson(route('teacher.course.lesson.destroy', [$course, 'non-existing-slug']))
                ->assertNotFound();
        });

        it('fails if the lesson is from another course', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();
            $lesson = Lesson::factory()->create();

            deleteJson(route('teacher.course.lesson.destroy', [$course, $lesson]))
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

            deleteJson(route('teacher.course.lesson.destroy', [$course, $lesson]))
                ->assertUnauthorized();

            $this->assertDatabaseHas('lessons', ['id' => $lesson->id]);
            $this->assertDatabaseHas($course->type->value . '_lessons', ['id' => $lesson->lessonable->id]);
        });

        it('fails if users without permissions try to delete a lesson', function ($user) {
            Sanctum::actingAs($user);

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            deleteJson(route('teacher.course.lesson.destroy', [$course, $lesson]))
                ->assertForbidden();

            $this->assertDatabaseHas('lessons', ['id' => $lesson->id]);
            $this->assertDatabaseHas($course->type->value . '_lessons', ['id' => $lesson->lessonable->id]);
        })->with([
            'user' => fn() => User::factory()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
            'admin'           => fn() => User::factory()->admin()->create(),
        ]);

        it('allows users with permission to delete a lesson', function ($userClosure, $courseClosure) {
            $user = $userClosure();
            Sanctum::actingAs($user);

            $courseInnerClosure = $courseClosure();
            $course = $courseInnerClosure($user);
            $lesson = Lesson::factory()->for($course, 'course')->create();

            deleteJson(route('teacher.course.lesson.destroy', [$course, $lesson]))
                ->assertNoContent();

            $this->assertSoftDeleted('lessons', ['id' => $lesson->id]);
            $this->assertSoftDeleted($course->type->value . '_lessons', ['id' => $lesson->lessonable->id]);
        })->with([
            'teacher'     => fn() => User::factory()->teacher()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'published'   => fn() => fn($author) => Course::factory()->for($author, 'author')->create(),
            'unpublished' => fn() => fn($author) => Course::factory()->unpublished()->for($author, 'author')->create(),
            'banned'      => fn() => fn($author) => Course::factory()->banned()->for($author, 'author')->create(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the lesson cache when a lesson is deleted', function () {
            $teacher = User::factory()->teacher()->create();
            Sanctum::actingAs($teacher);

            $course = Course::factory()->for($teacher, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            $page = 1;
            $cacheKey = "lessons:course:{$course->id}:page:{$page}";
            $tags = [
                config('cache.tags.lesson_list'),
                config('cache.tags.course') . ':' . $course->id
            ];

            Cache::tags($tags)->put($cacheKey, 'test_value', config('cache.ttl.lesson'));
            expect(Cache::tags($tags)->get($cacheKey))->not->toBeNull();

            deleteJson(route('teacher.course.lesson.destroy', ['course' => $course->slug, 'lesson' => $lesson->slug]))
                ->assertNoContent();

            expect(Cache::tags($tags)->get($cacheKey))->toBeNull();
        });
    });
})->group('lesson', 'teacher');
