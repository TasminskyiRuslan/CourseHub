<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Seed the courses table.
     */
    public function run(): void
    {
        Course::factory()
            ->count(10)
            ->published()
            ->withImage()
            ->create();

        Course::factory()
            ->count(5)
            ->published()
            ->free()
            ->withImage()
            ->create();

        Course::factory()
            ->count(2)
            ->unpublished()
            ->withImage()
            ->create();
    }
}
