<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed prod data.
     */
    public function run(): void
    {

        $this->call([
            RolesAndPermissionsSeeder::class,
            SuperAdminUserSeeder::class,
        ]);
    }
}
