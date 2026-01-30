<?php

namespace App\Http\Resources\Api\Lessons;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $course_id
 * @property mixed $title
 * @property mixed $slug
 * @property mixed $position
 * @property mixed $lessonable
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class LessonResource extends JsonResource
{
    /**
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
            'content' => $this->lessonable?->toResource(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
