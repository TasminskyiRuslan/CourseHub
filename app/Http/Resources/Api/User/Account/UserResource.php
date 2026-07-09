<?php

namespace App\Http\Resources\Api\User\Account;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $slug
 * @property-read string $email
 * @property-read Carbon|null $email_verified_at
 * @property-read Collection|null $roles
 * @property-read string|null $avatar_path
 * @property-read Carbon|null $banned_at
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
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
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'roles' => $this->whenLoaded('roles', fn() => $this->roles->pluck('name')),
            'avatar_url' => $this->avatar_path ? Storage::disk('users')->url($this->avatar_path) : null,
            'banned_at' => $this->banned_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
