<?php

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('Admin -> CourseController -> index', function () {
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
        it('fails if an unauthenticated user tries to retrieve all courses', function () {
            getJson(route('admin.course.index'))
                ->assertUnauthorized();
        });

        it('fails if a user without permissions tries to retrieve all courses', function ($user) {
            Sanctum::actingAs($user);

            getJson(route('admin.course.index'))
                ->assertForbidden();
        })->with([
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
        ]);

        it('allows a user with permissions to retrieve all courses', function ($user) {
            Sanctum::actingAs($user);

            $courses = Course::factory()->count(3)->create();

            $response = getJson(route('admin.course.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => adminCourseJsonStructure()
                    ]
                ]);

            $responseDataIds = collect($response->json('data'))->pluck('id');
            foreach ($courses as $course) {
                expect($responseDataIds)->toContain($course->id);
            }
        })->with([
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | filters & sorting
    |--------------------------------------------------------------------------
    */
    describe('filters & sorting', function () {
        it('filters courses by a search string', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $course1 = Course::factory()->create(['title' => 'Laravel Deep Dive']);
            $course2 = Course::factory()->create(['title' => 'React Basics']);
            $searchString = substr($course1->title, 8);

            getJson(route('admin.course.index', ['filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonFragment(['id' => $course1->id])
                ->assertJsonMissing(['data' => [['id' => $course2->id]]])
                ->assertJsonStructure([
                    'data' => [
                        '*' => adminCourseJsonStructure()
                    ]
                ]);
        });

        it('filters courses by type', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $onlineCourse = Course::factory()->type(CourseType::ONLINE)->create();
            $videoCourse = Course::factory()->type(CourseType::VIDEO)->create();

            getJson(route('admin.course.index', ['filter[type]' => CourseType::ONLINE->value]))
                ->assertOk()
                ->assertJsonFragment(['id' => $onlineCourse->id])
                ->assertJsonMissing(['data' => [['id' => $videoCourse->id]]])
                ->assertJsonStructure([
                    'data' => [
                        '*' => adminCourseJsonStructure()
                    ]
                ]);
        });

        it('filters courses by author slug', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $teacher = User::factory()->teacher()->create();
            $course1 = Course::factory()->for($teacher, 'author')->create();
            $course2 = Course::factory()->create();

            getJson(route('admin.course.index', ['filter[author]' => $teacher->slug]))
                ->assertOk()
                ->assertJsonFragment(['id' => $course1->id])
                ->assertJsonMissing(['data' => [['id' => $course2->id]]])
                ->assertJsonStructure([
                    'data' => [
                        '*' => adminCourseJsonStructure()
                    ]
                ]);
        });

        it('filters courses by banned status', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $bannedCourse = Course::factory()->banned()->create();
            $activeCourse = Course::factory()->create();

            getJson(route('admin.course.index', ['filter[banned]' => 'true']))
                ->assertOk()
                ->assertJsonFragment(['id' => $bannedCourse->id])
                ->assertJsonMissing(['data' => [['id' => $activeCourse->id]]]);

            getJson(route('admin.course.index', ['filter[banned]' => 'false']))
                ->assertOk()
                ->assertJsonFragment(['id' => $activeCourse->id])
                ->assertJsonMissing(['data' => [['id' => $bannedCourse->id]]]);
        });

        it('filters courses by published status', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $publishedCourse = Course::factory()->create();
            $unpublishedCourse = Course::factory()->unpublished()->create();

            getJson(route('admin.course.index', ['filter[published]' => 'true']))
                ->assertOk()
                ->assertJsonFragment(['id' => $publishedCourse->id])
                ->assertJsonMissing(['data' => [['id' => $unpublishedCourse->id]]]);

            getJson(route('admin.course.index', ['filter[published]' => 'false']))
                ->assertOk()
                ->assertJsonFragment(['id' => $unpublishedCourse->id])
                ->assertJsonMissing(['data' => [['id' => $publishedCourse->id]]]);
        });

        it('filters courses by trashed records (soft deletes)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $activeCourse = Course::factory()->create();
            $trashedCourse = Course::factory()->create();
            $trashedCourse->delete();

            getJson(route('admin.course.index'))
                ->assertOk()
                ->assertJsonFragment(['id' => $activeCourse->id])
                ->assertJsonMissing(['data' => [['id' => $trashedCourse->id]]]);

            getJson(route('admin.course.index', ['filter[trashed]' => 'with']))
                ->assertOk()
                ->assertJsonFragment(['id' => $activeCourse->id])
                ->assertJsonFragment(['id' => $trashedCourse->id]);

            getJson(route('admin.course.index', ['filter[trashed]' => 'only']))
                ->assertOk()
                ->assertJsonFragment(['id' => $trashedCourse->id])
                ->assertJsonMissing(['data' => [['id' => $activeCourse->id]]]);
        });

        it('sorts courses by created_at (desc) by default', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $oldCourse = Course::factory()->create();
            $oldCourse->setCreatedAt(now()->subDays(2))->save();
            $newCourse = Course::factory()->create();
            $newCourse->setCreatedAt(now()->subDay())->save();

            $response = getJson(route('admin.course.index'))->assertOk();
            $ids = collect($response->json('data'))->pluck('id')->all();

            expect(array_search($newCourse->id, $ids))->toBeLessThan(array_search($oldCourse->id, $ids));
        });

        it('sorts courses by created_at (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $oldCourse = Course::factory()->create();
            $oldCourse->setCreatedAt(now()->subDays(2))->save();
            $newCourse = Course::factory()->create();
            $newCourse->setCreatedAt(now()->subDay())->save();

            $ascResponse = getJson(route('admin.course.index', ['sort' => 'created_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldCourse->id, $ascIds))->toBeLessThan(array_search($newCourse->id, $ascIds));

            $descResponse = getJson(route('admin.course.index', ['sort' => '-created_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newCourse->id, $descIds))->toBeLessThan(array_search($oldCourse->id, $descIds));
        });

        it('sorts courses by title (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $courseA = Course::factory()->create(['title' => 'CourseA']);
            $courseB = Course::factory()->create(['title' => 'CourseB']);

            $ascResponse = getJson(route('admin.course.index', ['sort' => 'title']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($courseA->id, $ascIds))->toBeLessThan(array_search($courseB->id, $ascIds));

            $descResponse = getJson(route('admin.course.index', ['sort' => '-title']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($courseB->id, $descIds))->toBeLessThan(array_search($courseA->id, $descIds));
        });

        it('sorts courses by price (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $cheap = Course::factory()->create(['price' => 100]);
            $expensive = Course::factory()->create(['price' => 500]);

            $ascResponse = getJson(route('admin.course.index', ['sort' => 'price']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($cheap->id, $ascIds))->toBeLessThan(array_search($expensive->id, $ascIds));

            $descResponse = getJson(route('admin.course.index', ['sort' => '-price']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($expensive->id, $descIds))->toBeLessThan(array_search($cheap->id, $descIds));
        });

        it('sorts courses by published_at (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $oldCourse = Course::factory()->create(['published_at' => now()->subDays(5)]);
            $newCourse = Course::factory()->create(['published_at' => now()->subDay()]);

            $ascResponse = getJson(route('admin.course.index', ['sort' => 'published_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldCourse->id, $ascIds))->toBeLessThan(array_search($newCourse->id, $ascIds));

            $descResponse = getJson(route('admin.course.index', ['sort' => '-published_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newCourse->id, $descIds))->toBeLessThan(array_search($oldCourse->id, $descIds));
        });

        it('sorts courses by banned_at (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $oldBanned = Course::factory()->create(['banned_at' => now()->subDays(5)]);
            $newBanned = Course::factory()->create(['banned_at' => now()->subDay()]);

            $ascResponse = getJson(route('admin.course.index', ['sort' => 'banned_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldBanned->id, $ascIds))->toBeLessThan(array_search($newBanned->id, $ascIds));

            $descResponse = getJson(route('admin.course.index', ['sort' => '-banned_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newBanned->id, $descIds))->toBeLessThan(array_search($oldBanned->id, $descIds));
        });

        it('sorts courses by deleted_at (asc and desc)', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            $oldDeleted = Course::factory()->create();
            $oldDeleted->delete();
            DB::table('courses')->where('id', $oldDeleted->id)->update(['deleted_at' => now()->subDays(5)]);

            $newDeleted = Course::factory()->create();
            $newDeleted->delete();
            DB::table('courses')->where('id', $newDeleted->id)->update(['deleted_at' => now()->subDay()]);

            $ascResponse = getJson(route('admin.course.index', ['filter[trashed]' => 'only', 'sort' => 'deleted_at']))->assertOk();
            $ascIds = collect($ascResponse->json('data'))->pluck('id')->all();
            expect(array_search($oldDeleted->id, $ascIds))->toBeLessThan(array_search($newDeleted->id, $ascIds));

            $descResponse = getJson(route('admin.course.index', ['filter[trashed]' => 'only', 'sort' => '-deleted_at']))->assertOk();
            $descIds = collect($descResponse->json('data'))->pluck('id')->all();
            expect(array_search($newDeleted->id, $descIds))->toBeLessThan(array_search($oldDeleted->id, $descIds));
        });

        it('returns empty data when no courses match the search', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            Course::factory()->create();

            getJson(route('admin.course.index', ['filter[search]' => 'non-existent']))
                ->assertOk()
                ->assertJsonCount(0, 'data');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | pagination
    |--------------------------------------------------------------------------
    */
    describe('pagination', function () {
        it('returns a paginated list of courses', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            Course::factory()->count(3)->create();

            getJson(route('admin.course.index'))
                ->assertOk()
                ->assertJsonStructure(paginationJsonStructure());
        });
    });
})->group('course', 'admin');
