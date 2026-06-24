<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('AccountAvatarController -> update', function () {

    beforeEach(function () {
        Cache::flush();
        Storage::fake('users');
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the required fields are missing', function () {
            $account = User::factory()->student()->create();
            Sanctum::actingAs($account);

            postJson(route('account.avatar.update'), ['_method' => 'PUT'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails if the image is not a file', function () {
            $account = User::factory()->student()->create();
            Sanctum::actingAs($account);

            postJson(route('account.avatar.update'), imagePayload([
                'image' => 'not-a-file',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails if the file is not an image', function () {
            $account = User::factory()->student()->create();
            Sanctum::actingAs($account);

            postJson(route('account.avatar.update'), imagePayload([
                'image' => UploadedFile::fake()->create('document.pdf'),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails if the image exceeds the 2048KB size limit', function () {
            $account = User::factory()->student()->create();
            Sanctum::actingAs($account);

            postJson(route('account.avatar.update'), imagePayload([
                'image' => UploadedFile::fake()->create('avatar.jpg')->size(2049),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('succeeds if image uploads with all allowed extensions', function ($ext) {
            $account = User::factory()->student()->create();
            Sanctum::actingAs($account);

            postJson(route('account.avatar.update'), imagePayload([
                'image' => UploadedFile::fake()->image("avatar.$ext"),
            ]))
                ->assertOk()
                ->assertJsonStructure(['data' => userJsonStructure()]);

            $account->refresh();
            expect($account->avatar_path)->not->toBeNull();
            Storage::disk('users')->assertExists($account->avatar_path);
        })->with(['jpg', 'jpeg', 'png']);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to update the account image', function () {
            postJson(route('account.avatar.update'), imagePayload())
                ->assertUnauthorized();
        });

        it('fails if a super-admin tries to update the account avatar', function () {
            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            postJson(route('account.avatar.update'), imagePayload())
                ->assertUnprocessable();

            $superAdmin->refresh();
            expect($superAdmin->avatar_path)->toBeNull();
        });

        it('allows authenticated users with any role to update their own account image', function ($user) {
            Sanctum::actingAs($user);

            $data = imagePayload();

            postJson(route('account.avatar.update'), $data)
                ->assertOk()
                ->assertJsonStructure(['data' => userJsonStructure()]);

            $user->refresh();
            expect($user->avatar_path)->not->toBeNull();
            Storage::disk('users')->assertExists($user->avatar_path);
        })->with([
            'student' => fn() => User::factory()->student()->create(),
            'teacher' => fn() => User::factory()->teacher()->create(),
            'admin' => fn() => User::factory()->admin()->create()
        ]);
    });
})->group('account');
