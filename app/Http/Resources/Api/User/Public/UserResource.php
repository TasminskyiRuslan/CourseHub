<?php

namespace App\Http\Resources\Api\User\Public;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $slug
 * @property-read string|null $avatar_path
 * @property-read int|null $courses_count
 * @property-read Collection|null $roles
 */
class UserResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'roles'      => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'avatar_url' => $this->avatar_path ? Storage::disk('users')->url($this->avatar_path) : null,
            'courses_count' => $this->whenCounted('courses', fn () => $this->courses_count, 0),
        ];
    }
}
