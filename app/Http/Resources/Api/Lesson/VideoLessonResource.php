<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\Lesson;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $video_url
 * @property-read string $provider
 */
class VideoLessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'video_url' => $this->video_url,
            'provider' => $this->provider,
        ];
    }
}
