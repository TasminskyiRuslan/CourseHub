<?php

namespace App\DTO;

use App\Http\Requests\Api\UpdateCourseRequest; // Тільки UpdateRequest!
use Brick\Money\Money;
use Illuminate\Http\UploadedFile;

final readonly class UpdateCourseDTO
{
    public function __construct(
        public ?string       $title,
        public ?string       $slug,
        public ?string       $description,
        public ?Money        $price,
        public ?UploadedFile $image,
    ) {}

    public static function fromRequest(UpdateCourseRequest $request): self
    {
        return new self(
            title: $request->has('title') ? $request->string('title')->trim()->toString() : null,
            slug: $request->has('slug') ? $request->string('slug')->trim()->toString() : null,
            description: $request->has('description') ? $request->string('description')->trim()->toString() : null,
            price: $request->has('price')
                ? Money::of($request->string('price')->toString(), 'USD')
                : null,
            image: $request->file('image'),
        );
    }

    public function toArray(): array
    {

        return array_filter([
            'title'       => $this->title,
            'slug'        => $this->slug,
            'description' => $this->description,
            'price'       => $this->price?->getAmount()->toScale(2),
            'image'       => $this->image,
        ], fn($value) => !is_null($value));
    }
}
