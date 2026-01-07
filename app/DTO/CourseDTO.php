<?php

namespace App\DTO;

use App\Enums\CourseType;
use App\Http\Requests\Api\StoreCourseRequest;
use App\Http\Requests\Api\UpdateCourseRequest;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use Illuminate\Http\UploadedFile;

final readonly class CourseDTO
{
    public function __construct(
        public string        $title,
        public CourseType    $type,
        public ?string       $slug,
        public ?string       $description,
        public Money         $price,
        public ?UploadedFile $image,
    ) {}

    /**
     * @throws UnknownCurrencyException
     */
    public static function fromRequest(StoreCourseRequest|UpdateCourseRequest $request): self
    {
        return new self(
            title: $request->string('title')->trim(),
            type: $request->enum('type', CourseType::class),
            slug: $request->string('slug')->trim() ?: null,
            description: $request->string('description')->trim() ?: null,
            price: Money::of($request->string('price', '0.00'), 'USD'),
            image: $request->file('image'),
        );
    }

    public function toArray(): array
    {
        return [
            'title'       => $this->title,
            'type'        => $this->type->value,
            'slug'        => $this->slug,
            'description' => $this->description,
            'price'       => $this->price->getAmount()->toScale(2),
            'image'       => $this->image,
        ];
    }
}
