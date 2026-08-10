<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Hash;
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
            'password' => static::$password ??= Hash::make('password'),
            'banned_at' => null,
            'avatar_path' => null,
        ];
    }

    /**
     * Indicate that the user is unverified.
     *
     * @return $this
     */
    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is banned.
     *
     * @return $this
     */
    public function banned(): static
    {
        return $this->state(fn () => [
            'banned_at' => now(),
        ]);
    }

    /**
     * Indicate that the user has the teacher role.
     *
     * @return $this
     */
    public function teacher(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->syncRoles(UserRole::TEACHER->value);
        });
    }

    /**
     * Indicate that the user has the admin role.
     *
     * @return $this
     */
    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->syncRoles(UserRole::ADMIN->value);
        });
    }

    /**
     * Add an avatar to the user.
     */
    public function withAvatar(?string $path = null): static
    {
        return $this->state(function (array $attributes) use ($path) {
            return ['avatar_path' => $path ?? 'users/'.fake()->uuid().'.jpg'];
        });
    }
}
