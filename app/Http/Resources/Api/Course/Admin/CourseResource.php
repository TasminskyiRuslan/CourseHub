<?php

namespace App\Http\Resources\Api\Course\Admin;

use App\Http\Resources\Api\User\Admin\UserResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read int $id
 * @property-read int|null $author_id
 * @property-read UserResource|MissingValue $author
 * @property-read string $title
 * @property-read string $slug
 * @property-read string|null $description
 * @property-read string $type
 * @property-read string $price
 * @property-read string|null $image_path
 * @property-read int|MissingValue $lessons_count
 * @property-read Carbon|null $published_at
 * @property-read Carbon|null $banned_at
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon|null $deleted_at
 */
class CourseResource extends JsonResource
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
            'author_id' => $this->author_id,
            'author' => UserResource::make($this->whenLoaded('author')),
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => $this->type,
            'price' => $this->price,
            'image_url' => $this->image_path ? Storage::disk('courses')->url($this->image_path) : null,
            'lessons_count' => $this->whenCounted('lessons', fn() => $this->lessons_count, 0),
            'published_at' => $this->published_at,
            'banned_at' => $this->banned_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
