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
            'title' => fake()->words(3, true),
            'slug' => null,
            'description' => fake()->sentence(10),
            'price' => fake()->randomFloat(2, 0, 500),
            'type' => fake()->randomElement(CourseType::cases()),
            'image_path' => null,
            'is_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn() => ['is_published' => true]);
    }

    public function unpublished(): static
    {
        return $this->state(fn() => ['is_published' => false]);
    }

    public function free(): static
    {
        return $this->state(fn() => ['price' => 0]);
    }

    public function withImage(string $path): static
    {
        return $this->state(fn() => ['image_path' => $path]);
    }

    public function type(CourseType $courseType): static
    {
        return $this->state(fn() => ['type' => $courseType]);
    }
}
