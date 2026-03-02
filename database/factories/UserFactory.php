<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password',
            'role' => fake()->randomElement([UserRole::STUDENT, UserRole::TEACHER]),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn() => [
            'email_verified_at' => null,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn() => [
            'email_verified_at' => now(),
        ]);
    }

    public function student(): static
    {
        return $this->state(fn() => [
            'role' => UserRole::STUDENT,
        ]);
    }

    public function teacher(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::TEACHER,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::ADMIN,
        ]);
    }
}
