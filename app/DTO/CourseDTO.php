<?php

namespace App\DTO;

use App\Enums\CourseType;
use App\Http\Requests\Api\StoreCourseRequest;
use App\Http\Requests\Api\UpdateCourseRequest;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use Illuminate\Http\UploadedFile;

readonly class CourseDTO
{
    public function __construct(
        public int           $userId,
        public string        $title,
        public CourseType    $type,
        public ?string       $slug,
        public ?string       $description,
        public Money         $price,
        public ?UploadedFile $image,
    )
    {
    }

    /**
     * @throws UnknownCurrencyException
     */
    public static function fromRequest(StoreCourseRequest|UpdateCourseRequest $request): self
    {
        $validated = $request->safe();

        return new self(
            userId: $request->user()->id,
            title: $validated->title,
            type: CourseType::from($validated->type),
            slug: $validated->slug,
            description: $validated->description,
            price: Money::of($validated->price ?? '0.00', 'USD'),
            image: $request->file('image'),
        );
    }
}
