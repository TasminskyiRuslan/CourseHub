<?php

namespace App\DTO\Courses;

use App\Http\Requests\Api\Courses\UpdateCourseRequest;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;

// Тільки UpdateRequest!

final readonly class UpdateCourseDTO
{
    public function __construct(
        public string        $title,
        public ?string       $slug,
        public ?string       $description,
        public Money         $price,
    ) {}

    /**
     * @throws UnknownCurrencyException
     */
    public static function fromRequest(UpdateCourseRequest $request): self
    {
        return new self(
            title: $request->input('title'),
            slug: $request->input('slug'),
            description: $request->input('description'),
            price: Money::of($request->input('price'), 'USD'),
        );
    }

    public function toArray(): array
    {
        return [
            'title'       => $this->title,
            'slug'        => $this->slug,
            'description' => $this->description,
            'price'       => $this->price->getAmount()->toScale(2),
        ];
    }
}
