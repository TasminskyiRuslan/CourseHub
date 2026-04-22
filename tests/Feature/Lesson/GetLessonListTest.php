<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('LessonController -> index', function () {
    beforeEach(function () {
        Cache::flush();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('allows users to retrieve lessons of only published courses', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $publishedCourse = Course::factory()->create();
            $publishedLessons = Lesson::factory()->count(3)->for($publishedCourse, 'course')->create();

            $unpublishedCourse = Course::factory()->unpublished()->create();
            $unpublishedLessons = Lesson::factory()->count(2)->for($unpublishedCourse, 'course')->create();

            getJson(route('course.lesson.index', $publishedCourse))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => lessonJsonStructure($publishedLessons->first()->course->type),
                    ]
                ])
                ->assertJsonCount($publishedLessons->count(), 'data');

            getJson(route('course.lesson.index', $unpublishedCourse))
                ->assertForbidden();
        })->with([
            'guest' => null,
            'unverified' => fn() => User::factory()->unverified()->create(),
            'student' => fn() => User::factory()->student()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows the author to retrieve lessons of their own unpublished courses', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $ownUnpublishedCourse = Course::factory()->unpublished()->for($author, 'author')->create();
            $ownUnpublishedLessons = Lesson::factory()->count(3)->for($ownUnpublishedCourse, 'course')->create();

            getJson(route('course.lesson.index', $ownUnpublishedCourse))
                ->assertOk()
                ->assertJsonCount($ownUnpublishedLessons->count(), 'data');
        });

        it('allows admins to retrieve lessons of any unpublished courses', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $unpublishedCourse = Course::factory()->unpublished()->create();
            $unpublishedLessons = Lesson::factory()->count(4)->for($unpublishedCourse, 'course')->create();

            getJson(route('course.lesson.index', $unpublishedCourse))
                ->assertOk()
                ->assertJsonCount($unpublishedLessons->count(), 'data');
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | filters & sorting
    |--------------------------------------------------------------------------
    */
    describe('filters & sorting', function () {
        it('filters lessons by a search string', function () {
            $course = Course::factory()->create();
            $lesson1 = Lesson::factory()->for($course, 'course')->create(['title' => 'Introduction to Laravel']);
            $lesson2 = Lesson::factory()->for($course, 'course')->create(['title' => 'React hooks']);
            $searchString = substr($lesson1->title, 7);;

            getJson(route('course.lesson.index', [$course, 'filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment(['id' => $lesson1->id]);
        });

        it('sorts lessons by position (asc) by default', function () {
            $course = Course::factory()->create();
            $lesson2 = Lesson::factory()->for($course, 'course')->create(['position' => 2]);
            $lesson1 = Lesson::factory()->for($course, 'course')->create(['position' => 1]);

            getJson(route('course.lesson.index', $course))
                ->assertOk()
                ->assertJsonPath('data.0.id', $lesson1->id)
                ->assertJsonPath('data.1.id', $lesson2->id);
        });

        it('sorts lessons by created_at (asc and desc)', function () {
            $course = Course::factory()->create();
            $oldLesson = Lesson::factory()->for($course, 'course')->create();
            $oldLesson->setCreatedAt(now()->subDays(2))->save();
            $newLesson = Lesson::factory()->for($course, 'course')->create();
            $newLesson->setCreatedAt(now()->subDay())->save();

            getJson(route('course.lesson.index', [$course, 'sort' => 'created_at']))
                ->assertOk()
                ->assertJsonPath('data.0.id', $oldLesson->id)
                ->assertJsonPath('data.1.id', $newLesson->id);

            getJson(route('course.lesson.index', [$course, 'sort' => '-created_at']))
                ->assertOk()
                ->assertJsonPath('data.0.id', $newLesson->id)
                ->assertJsonPath('data.1.id', $oldLesson->id);
        });

        it('sorts lessons by title (asc and desc)', function () {
            $course = Course::factory()->create();
            $lessonA = Lesson::factory()->for($course, 'course')->create(['title' => 'LessonA']);
            $lessonB = Lesson::factory()->for($course, 'course')->create(['title' => 'LessonB']);
            $lessonC = Lesson::factory()->for($course, 'course')->create(['title' => 'LessonC']);

            getJson(route('course.lesson.index', [$course, 'sort' => 'title']))
                ->assertOk()
                ->assertJsonPath('data.0.id', $lessonA->id)
                ->assertJsonPath('data.1.id', $lessonB->id)
                ->assertJsonPath('data.2.id', $lessonC->id);

            getJson(route('course.lesson.index', [$course, 'sort' => '-title']))
                ->assertOk()
                ->assertJsonPath('data.0.id', $lessonC->id)
                ->assertJsonPath('data.1.id', $lessonB->id)
                ->assertJsonPath('data.2.id', $lessonA->id);
        });

        it('returns empty data when no lessons match the search', function () {
            $course = Course::factory()->create();
            $lesson = Lesson::factory()->for($course, 'course')->create();

            getJson(route('course.lesson.index', [$course, 'filter[search]' => 'non-existent']))
                ->assertOk()
                ->assertJsonCount(0, 'data');
        });

        it('returns not found for non-existing course', function () {
            getJson(route('course.lesson.index', 'non-existing-slug'))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('stores the lesson list in the cache after the first request', function () {
            Cache::spy();
            $course = Course::factory()->create();

            getJson(route('course.lesson.index', $course))->assertOk();
            Cache::shouldHaveReceived('tags')
                ->with([
                    config('cache.tags.lesson_list'),
                    config('cache.tags.course') . ':' . $course->id
                ])
                ->once();
        });

        it('returns data from the cache instead of the database on subsequent requests', function () {
            $course = Course::factory()->create();
            $oldTitle = 'LessonA';
            $lesson = Lesson::factory()->for($course, 'course')->create(['title' => $oldTitle]);

            getJson(route('course.lesson.index', $course))->assertOk();

            $newTitle = 'LessonB';
            DB::table('lessons')->update(['title' => $newTitle]);

            getJson(route('course.lesson.index', $course))
                ->assertOk()
                ->assertJsonFragment(['title' => $oldTitle])
                ->assertJsonMissing(['title' => $newTitle]);
        });

        it('does not store the lesson list in the cache users for author of course', function () {
            $author = User::factory()->teacher()->create();
            Sanctum::actingAs($author);

            $course = Course::factory()->unpublished()->for($author, 'author')->create();

            Cache::spy();
            getJson(route('course.lesson.index', $course))->assertOk();
            Cache::shouldNotHaveReceived('tags');
        });

        it('does not store the lesson list in the cache for users that can view-unpublished courses', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }
            $course = Course::factory()->unpublished()->create();

            Cache::spy();
            getJson(route('course.lesson.index', $course))->assertOk();
            Cache::shouldNotHaveReceived('tags');
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::whereEmail(config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | pagination
    |--------------------------------------------------------------------------
    */
    describe('pagination', function () {
        it('returns a paginated list of lessons', function () {
            $course = Course::factory()->create();
            $lessons = Lesson::factory()->count(7)->for($course)->create();

            getJson(route('course.lesson.index', $course))
                ->assertOk()
                ->assertJsonStructure(paginationJsonStructure());
        });
    });
})->group('lesson');
