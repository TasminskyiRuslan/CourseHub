<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\Course\Public;

use App\Http\Resources\Api\User\Public\TeacherResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read int $id
 * @property-read int|null $author_id
 * @property-read TeacherResource|MissingValue $author
 * @property-read string $title
 * @property-read string $slug
 * @property-read string|null $description
 * @property-read string $type
 * @property-read float $price
 * @property-read string|null $image_path
 * @property-read int|MissingValue $lessons_count
 * @property-read Carbon|null $published_at
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
            'author' => TeacherResource::make($this->whenLoaded('author')),
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => $this->type,
            'price' => $this->price,
            'image_url' => $this->image_path ? Storage::disk('courses')->url($this->image_path) : null,
            'lessons_count' => $this->whenCounted('lessons'),
            'published_at' => $this->published_at,
        ];
    }
}
