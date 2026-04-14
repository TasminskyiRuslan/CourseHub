<?php

namespace Database\Factories;

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $title = fake()->sentence(3);
        return [
            'author_id' => User::factory()->lazy(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->sentence(10),
            'price' => fake()->randomFloat(2, 0, 500),
            'type' => fake()->randomElement(CourseType::cases()),
            'image_path' => null,
            'is_published' => false,
        ];
    }

    /**
     * Indicate that the course is published.
     *
     * @return $this
     */
    public function published(): static
    {
        return $this->state(fn() => ['is_published' => true]);
    }

    /**
     * Indicate that the course is unpublished.
     *
     * @return $this
     */
    public function unpublished(): static
    {
        return $this->state(fn() => ['is_published' => false]);
    }

    /**
     * Indicate that the course is free.
     *
     * @return $this
     */
    public function free(): static
    {
        return $this->state(fn() => ['price' => 0]);
    }

    /**
     * Add an image to the book.
     *
     * @param string|null $path
     * @return static
     */
    public function withImage(?string $path = null): static
    {
        return $this->state(function (array $attributes) use ($path) {
            return ['image_path' => $path ?? 'courses/' . fake()->uuid() . '.jpg'];
        });
    }

    /**
     * Indicate that the user has some type.
     *
     * @return $this
     */
    public function type(CourseType $courseType): static
    {
        return $this->state(fn() => ['type' => $courseType]);
    }
}
