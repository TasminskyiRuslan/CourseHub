<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Hash;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

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
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            if ($user->roles->isEmpty()) {
                $user->assignRole(UserRole::STUDENT->value);
            }
        });
    }

    /**
     * Indicate that the user is unverified.
     *
     * @return $this
     */
    public function unverified(): static
    {
        return $this->state(fn() => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is verified.
     *
     * @return $this
     */
    public function verified(): static
    {
        return $this->state(fn() => [
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the user has the student role.
     *
     * @return $this
     */
    public function student(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->syncRoles(UserRole::STUDENT->value);
        });
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
}
