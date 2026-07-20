<?php

namespace App\Http\Resources\Api\Auth;

use App\Http\Resources\Api\User\Account\UserResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read UserResource $user
 * @property-read string $accessToken
 * @property-read string|null $tokenType
 * @property-read Carbon|null $expiresAt
 */
class AuthResource extends JsonResource
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
            'user' => UserResource::make($this->user),
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType ?? 'Bearer',
            'expires_at' => $this->expiresAt,
        ];
    }
}
