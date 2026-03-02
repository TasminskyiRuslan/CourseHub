<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * UserSeeder handles the initial population of the users table.
     */
    public function run(): void
    {
        User::factory()
            ->count(5)
            ->teacher()
            ->create();

        User::factory()
            ->count(20)
            ->student()
            ->create();

        User::factory()
            ->count(3)
            ->unverified()
            ->create();
    }
}
