<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $user
 * @property mixed $token
 * @property mixed $expiresAt
 */
class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => $this->user->toResource(),
            'access_token' => $this->token,
            'token_type' => 'Bearer',
            'expires_at' => $this->expiresAt,
        ];
    }
}
