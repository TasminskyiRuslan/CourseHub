<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $title
 * @property mixed $slug
 * @property mixed $position
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property mixed $lessonable
 * @property mixed $lessonable_type
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
            'title' => $this->title,
            'slug' => $this->slug,
            'position' => $this->position,
            'content' => $this->whenLoaded('lessonable', function () {
                return $this->lessonable->toResource();
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
