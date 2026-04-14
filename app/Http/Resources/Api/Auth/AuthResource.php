<?php

namespace App\Http\Resources\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read  mixed $user
 * @property-read  mixed $accessToken
 * @property-read string|null $tokenType
 * @property-read  mixed $expiresAt
 */
class AuthResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => UserResource::make($this->user),
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType ?? 'Bearer',
            'expires_at' => $this->expiresAt->toIso8601String(),
        ];
    }
}
