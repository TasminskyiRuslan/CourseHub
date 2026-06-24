<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('AccountController -> update', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminUserSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if name is too short', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            patchJson(route('account.update'), ['name' => 'A'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });

        it('fails if name is too long', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            patchJson(route('account.update'), ['name' => str_repeat('A', 101)])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });

        it('fails if slug format is invalid', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            patchJson(route('account.update'), ['slug' => 'Invalid Slug!'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if slug is already taken', function () {
            $user = User::factory()->create(['slug' => 'my-slug']);
            $anotherUser = User::factory()->create(['slug' => 'taken-slug']);

            Sanctum::actingAs($user);

            patchJson(route('account.update'), ['slug' => $anotherUser->slug])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if a super-admin tries to update their own account data', function () {
            $superAdmin = User::whereEmail(config('super-admin.email'))->first();
            Sanctum::actingAs($superAdmin);

            $data = [
                'name' => 'New Super Name',
                'slug' => 'new-super-slug'
            ];

            patchJson(route('account.update'), $data)
                ->assertUnprocessable();

            $this->assertDatabaseHas('users', [
                'email' => config('super-admin.email'),
                'name' => config('super-admin.name'),
                'slug' => config('super-admin.slug'),
            ]);
        });

        it('allows the authenticated user to update their own account', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $data = [
                'name' => 'New Name',
                'slug' => 'new-slug'
            ];

            patchJson(route('account.update'), $data)
                ->assertOk()
                ->assertJsonStructure([
                    'data' => userJsonStructure()
                ])
                ->assertJsonPath('data.name', $data['name'])
                ->assertJsonPath('data.slug', $data['slug']);

            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'name' => $data['name'],
                'slug' => $data['slug']
            ]);
        });
    });
})->group('account');
