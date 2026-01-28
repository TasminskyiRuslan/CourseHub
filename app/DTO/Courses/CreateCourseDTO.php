<?php

namespace App\DTO\Courses;

use App\Enums\CourseType;
use App\Http\Requests\Api\Courses\StoreCourseRequest;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;

final readonly class CreateCourseDTO
{
    public function __construct(
        public string        $title,
        public ?string       $slug,
        public ?string       $description,
        public CourseType    $type,
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
            slug: $request->input('slug'),
            description: $request->input('description'),
            type: $request->enum('type', CourseType::class),
            price: Money::of($request->input('price'), 'USD'),
        );
    }

    public function toArray(): array
    {
        return [
            'title'       => $this->title,
            'slug'        => $this->slug,
            'description' => $this->description,
            'type'        => $this->type->value,
            'price'       => $this->price->getAmount()->toScale(2),
        ];
    }
}
