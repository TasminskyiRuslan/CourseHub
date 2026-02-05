<?php

namespace App\Data\Auth;

use App\Data\Casts\LowercaseCast;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ForgotPasswordData extends Data
{
    public function __construct(
        #[Required]
        #[Email]
        #[Max(255)]
        #[WithCast(LowercaseCast::class)]
        public string $email,
    )
    {
    }
}
