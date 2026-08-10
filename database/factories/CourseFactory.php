<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CourseType;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     *
     * @throws ApiErrorException
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'author_id' => User::factory()->teacher()->lazy(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->sentence(10),
            'price' => (string) fake()->randomFloat(2, 10, 500),
            'stripe_product_id' => 'prod_fake_'.Str::random(14),
            'stripe_price_id' => 'price_fake_'.Str::random(14),
            'type' => fake()->randomElement(CourseType::cases()),
            'image_path' => null,
            'published_at' => now(),
            'banned_at' => null,
        ];
    }

    /**
     * Add the course to the Stripe service.
     *
     * @return $this
     */
    public function withStripe(): static
    {
        return $this->state(function (array $attributes) {
            $stripe = app(StripeClient::class);

            $product = $stripe->products->create([
                'name' => $attributes['title'],
            ]);

            $stripePrice = $stripe->prices->create([
                'product' => $product->id,
                'unit_amount' => (int) (((float) $attributes['price']) * 100),
                'currency' => config('cashier.currency', 'usd'),
            ]);

            return [
                'stripe_product_id' => $product->id,
                'stripe_price_id' => $stripePrice->id,
            ];
        });
    }

    /**
     * Indicate that the course is unpublished.
     *
     * @return $this
     */
    public function unpublished(): static
    {
        return $this->state(fn () => ['published_at' => null]);
    }

    /**
     * Indicate that the course is unpublished.
     *
     * @return $this
     */
    public function banned(): static
    {
        return $this->state(fn () => ['banned_at' => now()]);
    }

    /**
     * Indicate that the course is free.
     *
     * @return $this
     */
    public function free(): static
    {
        return $this->state(fn () => ['price' => 0]);
    }

    /**
     * Add an image to the course.
     */
    public function withImage(?string $path = null): static
    {
        return $this->state(function (array $attributes) use ($path) {
            return ['image_path' => $path ?? 'courses/'.fake()->uuid().'.jpg'];
        });
    }

    /**
     * Indicate that the user has some type.
     *
     * @return $this
     */
    public function type(CourseType $courseType): static
    {
        return $this->state(fn () => ['type' => $courseType]);
    }
}
