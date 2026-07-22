<?php

namespace App\Data\Course\Results;

use App\Enums\CheckoutStatus;
use Spatie\LaravelData\Data;

final class CheckoutResultData extends Data
{
    /**
     * @param CheckoutStatus $status
     * @param int $courseId
     * @param string|null $checkoutUrl
     */
    public function __construct(
        public CheckoutStatus $status,
        public int $courseId,
        public ?string $checkoutUrl = null,
    ) {}
}
