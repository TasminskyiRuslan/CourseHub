<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\User\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $slug
 * @property-read string|null $avatar_path
 * @property-read int|MissingValue $courses_count
 */
class TeacherResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'avatar_url' => $this->avatar_path ? Storage::disk('users')->url($this->avatar_path) : null,
            'courses_count' => $this->whenCounted('courses'),
        ];
    }
}
