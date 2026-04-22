<?php

namespace App\Data\Auth\Requests;

use App\Data\Casts\LowercaseCast;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class SendPasswordResetEmailData extends Data
{
    /**
     * @param string $email
     */
    public function __construct(
        #[Required]
        #[Email]
        #[Max(255)]
        #[WithCast(castClass: LowercaseCast::class)]
        public string $email,
    )
    {
    }
}
