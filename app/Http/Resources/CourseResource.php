<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property mixed $id
 * @property mixed $author_id
 * @property mixed $title
 * @property mixed $type
 * @property mixed $slug
 * @property mixed $description
 * @property mixed $price
 * @property mixed $image_path
 * @property mixed $author
 * @property mixed $is_published
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class CourseResource extends JsonResource
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
            'author_id' => $this->author_id,
            'title' => $this->title,
            'type' => $this->type,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'image_path' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'author' => $this->whenLoaded('author', function () {
                return $this->author->toResource();
            }),
            'is_published' => $this->is_published,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
