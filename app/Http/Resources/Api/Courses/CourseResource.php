<?php

namespace App\Http\Resources\Api\Courses;

use App\Http\Resources\Api\Auth\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property mixed $id
 * @property mixed $author_id
 * @property mixed $author
 * @property mixed $title
 * @property mixed $type
 * @property mixed $slug
 * @property mixed $description
 * @property mixed $price
 * @property mixed $image_path
 * @property mixed $is_published
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class CourseResource extends JsonResource
{

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'author_id' => $this->author_id,
            'author' => new UserResource($this->whenLoaded('author')),
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => $this->type,
            'price' => $this->price,
            'image_url' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'is_published' => $this->is_published,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
