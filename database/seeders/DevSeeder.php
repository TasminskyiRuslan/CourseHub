<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    /**
     * Seed dev data.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CourseSeeder::class,
            LessonSeeder::class,
        ]);
    }
}
