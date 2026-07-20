<?php

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
     * @param Request $request
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
