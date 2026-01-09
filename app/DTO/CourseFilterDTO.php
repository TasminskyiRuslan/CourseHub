<?php

namespace App\DTO;

use App\Enums\CourseSortField;
use App\Enums\CourseType;
use App\Enums\SortOrder;
use App\Http\Requests\Api\CourseListRequest;

final readonly class CourseFilterDTO
{
    public function __construct(
        public ?CourseType $type,
        public ?string     $search,
        public ?string     $author,
        public ?CourseSortField $sort,
        public ?SortOrder  $order,
    ) {}

    public static function fromRequest(CourseListRequest $request): self
    {
        return new self(
            type: $request->enum('type', CourseType::class),
            search: $request->string('search')->trim() ?: null,
            author: $request->string('author')->trim() ?: null,
            sort: $request->enum('sort', CourseSortField::class),
            order: $request->enum('order', SortOrder::class),
        );
    }

    public function toArray(): array
    {
        return [
            'type'   => $this->type?->value,
            'search' => $this->search,
            'author' => $this->author,
            'sort'   => $this->sort,
            'order'  => $this->order?->value,
        ];
    }
}
