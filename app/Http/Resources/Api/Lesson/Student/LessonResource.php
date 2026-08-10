<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\Lesson\Student;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

/**
 * @property-read int $id
 * @property-read int $course_id
 * @property-read string $title
 * @property-read string $slug
 * @property-read int $position
 * @property-read (Model&object{toResource: callable})|MissingValue|null $lessonable
 */
class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'position' => $this->position,
            'content' => $this->whenLoaded('lessonable', fn () => $this->lessonable?->toResource()),
        ];
    }
}
