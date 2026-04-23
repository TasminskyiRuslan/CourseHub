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

describe('LessonController -> destroy', function () {
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
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $lesson = Lesson::factory()->create();

            deleteJson(route('course.lesson.destroy', ['non-existing-slug', $lesson]))
                ->assertNotFound();
        });

        it('fails if the lesson does not exist', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->create();

            deleteJson(route('course.lesson.destroy', [$course, 'non-existing-slug']))
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

            deleteJson(route('course.lesson.destroy', [$course, $lesson]))
                ->assertUnauthorized();

            $this->assertDatabaseHas('lessons', ['id' => $lesson->id]);
            $this->assertDatabaseHas($course->type->value . '_lessons', ['id' => $lesson->lessonable->id]);
        });

        it('fails if users without permissions tries to delete someone else\'s lesson', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            deleteJson(route('course.lesson.destroy', [$course, $lesson]))
                ->assertForbidden();

            $this->assertDatabaseHas('lessons', ['id' => $lesson->id]);
            $this->assertDatabaseHas($course->type->value . '_lessons', ['id' => $lesson->lessonable->id]);
        })->with([
            'student' => fn() => User::factory()->student()->create(),
            'unverified teacher' => fn() => User::factory()->teacher()->unverified()->create(),
            'another teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows author of the course to delete the lesson', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->for($author, 'author')->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            deleteJson(route('course.lesson.destroy', [$course, $lesson]))
                ->assertNoContent();

            $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
            $this->assertDatabaseMissing($course->type->value . '_lessons', ['id' => $lesson->lessonable->id]);

        });

        it('allows users with permissions to delete any lesson', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            deleteJson(route('course.lesson.destroy', [$course, $lesson]))
                ->assertNoContent();

            $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
            $this->assertDatabaseMissing($course->type->value . '_lessons', ['id' => $lesson->lessonable->id]);
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    it('flushes the lesson cache when a lesson is deleted', function () {
        $author = User::factory()->teacher()->create();
        Sanctum::actingAs($author);

        $course = Course::factory()->for($author, 'author')->create();
        $lesson = Lesson::factory()->for($course, 'course')->create();

        Cache::tags([
            config('cache.tags.lesson_list'),
            config('cache.tags.course') . ':' . $course->id
        ])
            ->put('lessons', 'test_value', config('cache.ttl.lesson'));
        expect(Cache::tags([
            config('cache.tags.lesson_list'),
            config('cache.tags.course') . ':' . $course->id
        ])->get('lessons'))->not->toBeNull();

        deleteJson(route('course.lesson.destroy', [$course, $lesson]))
            ->assertNoContent();
        expect(Cache::tags([
            config('cache.tags.lesson_list'),
            config('cache.tags.course') . ':' . $course->id
        ])->get('lessons'))->toBeNull();
    });
})->group('lesson');
