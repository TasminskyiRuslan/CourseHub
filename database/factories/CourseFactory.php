<?php

namespace Database\Factories;

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => User::factory()->lazy(),
            'title' => fake()->sentence(3),
            'slug' => null,
            'description' => fake()->sentence(10),
            'price' => fake()->randomFloat(2, 0, 500),
            'type' => fake()->randomElement(CourseType::cases()),
            'image_path' => null,
            'is_published' => false,
        ];
    }

    public function published(): CourseFactory|Factory
    {
        return $this->state(fn () => ['is_published' => true]);
    }
}
