<?php

declare(strict_types=1);

namespace App\Enums;

enum CheckoutStatus: string
{
    case ENROLLED = 'enrolled';
    case PAYMENT_REQUIRED = 'payment_required';

    /**
     * Checks if the user is enrolled for the course.
     */
    public function isEnrolled(): bool
    {
        return $this === self::ENROLLED;
    }
}
