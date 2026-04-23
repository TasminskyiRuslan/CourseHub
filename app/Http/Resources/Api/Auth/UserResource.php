<?php

namespace App\Http\Resources\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read mixed $id
 * @property-read mixed $name
 * @property-read mixed $slug
 * @property-read mixed $email
 * @property-read mixed $roles
 * @property-read mixed $email_verified_at
 */
class UserResource extends JsonResource
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
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'role' => $this->roles->first()?->name,
        ];
    }
}
