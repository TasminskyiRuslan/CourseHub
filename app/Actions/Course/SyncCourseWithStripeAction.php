<?php

declare(strict_types=1);

namespace App\Actions\Course;

use App\Models\Course;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

readonly class SyncCourseWithStripeAction
{
    public function __construct(
        private StripeClient $stripe
    ) {}

    /**
     * Synchronizes the course with the Stripe service.
     *
     * @throws ApiErrorException
     */
    public function handle(Course $course): Course
    {
        if ($course->isFree()) {
            if ($course->stripe_price_id !== null) {
                $course->updateQuietly([
                    'stripe_price_id' => null,
                ]);
            }

            return $course;
        }

        $productId = $this->resolveProductId($course);
        $priceId = $this->resolvePriceId($course, $productId);

        $course->updateQuietly([
            'stripe_product_id' => $productId,
            'stripe_price_id' => $priceId,
        ]);

        return $course;
    }

    /**
     * Create or update a Stripe product.
     *
     * @throws ApiErrorException
     */
    private function resolveProductId(Course $course): string
    {
        if (! $course->stripe_product_id) {
            $product = $this->stripe->products->create([
                'name' => $course->title,
                'metadata' => [
                    'course_id' => (string) $course->id,
                ],
            ]);

            return $product->id;
        }

        $this->stripe->products->update($course->stripe_product_id, [
            'name' => $course->title,
        ]);

        return $course->stripe_product_id;
    }

    /**
     * Return an existing or create a Stripe price ID.
     *
     * @throws ApiErrorException
     */
    private function resolvePriceId(Course $course, string $productId): string
    {
        $amount = (int) round((float) $course->price * 100);
        $currency = config('cashier.currency');

        if ($course->stripe_price_id) {
            $currentPrice = $this->stripe->prices->retrieve($course->stripe_price_id);

            if ($currentPrice->unit_amount === $amount && $currentPrice->currency === $currency) {
                return $course->stripe_price_id;
            }

            $this->stripe->prices->update($course->stripe_price_id, [
                'active' => false,
            ]);
        }

        $price = $this->stripe->prices->create([
            'product' => $productId,
            'unit_amount' => $amount,
            'currency' => $currency,
        ]);

        return $price->id;
    }
}
