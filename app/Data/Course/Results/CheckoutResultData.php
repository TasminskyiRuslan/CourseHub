<?php

declare(strict_types=1);

namespace App\Data\Course\Results;

use App\Enums\CheckoutStatus;
use Spatie\LaravelData\Data;

final class CheckoutResultData extends Data
{
    public function __construct(
        public CheckoutStatus $status,
        public int $courseId,
        public ?string $checkoutUrl = null,
    ) {}
}
