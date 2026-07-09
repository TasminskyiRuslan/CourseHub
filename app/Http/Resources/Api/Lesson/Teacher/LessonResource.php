<?php

namespace App\Http\Resources\Api\Lesson\Teacher;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read int $id
 * @property-read int $course_id
 * @property-read string $title
 * @property-read string $slug
 * @property-read int $position
 * @property-read mixed $lessonable
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
