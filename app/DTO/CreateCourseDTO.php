<?php

namespace App\DTO;

use App\Enums\CourseType;
use App\Http\Requests\Api\StoreCourseRequest;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;

final readonly class CreateCourseDTO
{
    public function __construct(
        public string        $title,
        public CourseType    $type,
        public ?string       $slug,
        public ?string       $description,
        public Money         $price,
    )
    {
    }

    /**
     * @throws UnknownCurrencyException
     */
    public static function fromRequest(StoreCourseRequest $request): self
    {
        return new self(
            title: $request->input('title'),
            type: $request->enum('type', CourseType::class),
            slug: $request->input('slug'),
            description: $request->input('description'),
            price: Money::of($request->input('price'), 'USD'),
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
        ];
    }
}
