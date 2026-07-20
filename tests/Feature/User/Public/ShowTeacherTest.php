<?php

use App\Models\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('Public -> TeacherController -> show', function () {
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
        it('fails if the teacher does not exist', function () {
            getJson(route('teacher.show', 'non-existing-slug'))
                ->assertNotFound();
        });

        it('fails if the user is active but is not a teacher', function () {
            $user = User::factory()->create();

            getJson(route('teacher.show', $user))
                ->assertNotFound();
        });

        it('fails if a user tries to retrieve a restricted teacher', function ($userClosure, $targetTeacherClosure) {
            $user = $userClosure ? $userClosure() : null;
            $targetTeacher = $targetTeacherClosure();

            Course::factory()->for($targetTeacher, 'author')->create();

            if ($user) {
                Sanctum::actingAs($user);
            }

            getJson(route('teacher.show', $targetTeacher))
                ->assertNotFound();
        })->with([
            'guest' => null,
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ])->with([
            'banned teacher' => fn() => User::factory()->teacher()->banned()->create(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('allows any user to retrieve an active teacher', function ($user) {
            if ($user) {
                Sanctum::actingAs($user);
            }

            $targetTeacher = User::factory()->teacher()->create();
            Course::factory()->for($targetTeacher, 'author')->create();

            getJson(route('teacher.show', $targetTeacher))
                ->assertOk()
                ->assertJsonStructure(['data' => publicUserJsonStructure()]);
        })->with([
            'guest' => null,
            'user' => fn() => User::factory()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create(),
            'super-admin' => fn() => User::where('email', config('super-admin.email'))->first(),
        ]);
    });
})->group('user', 'public');
