<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminUserSeeder extends Seeder
{
    /**
     * Seed the super admin user.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => config('super-admin.email')],
            [
                'name' => config('super-admin.name'),
                'slug' => Str::slug(config('super-admin.name')),
                'password' => Hash::make(config('super-admin.password')),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole(UserRole::SUPER_ADMIN->value);
    }
}
