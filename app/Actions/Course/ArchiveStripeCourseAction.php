<?php

declare(strict_types=1);

namespace App\Actions\Course;

use App\Models\Course;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

readonly class ArchiveStripeCourseAction
{
    public function __construct(
        private StripeClient $stripe
    ) {}

    /**
     * Archive the course product in Stripe.
     *
     * @throws ApiErrorException
     */
    public function handle(string $stripeProductId): void
    {
        $this->stripe->products->update($stripeProductId, [
            'active' => false,
        ]);
    }
}
