<?php

namespace App\Data\Courses;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\Validation\Image;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Mimes;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class UpdateCourseImageData extends Data
{
    public function __construct(
        #[Required]
        #[Image]
        #[Mimes(['jpg', 'jpeg', 'png', 'webp'])]
        #[Max(2048)]
        public UploadedFile $image,
    ) {}
}
