<?php

namespace App\Http\Resources\Api\Lesson;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $video_url
 * @property mixed $provider
 */
class VideoLessonResource extends JsonResource
{
    /**
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
