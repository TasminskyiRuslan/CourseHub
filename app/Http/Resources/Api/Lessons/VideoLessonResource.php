<?php

namespace App\Http\Resources\Api\Lessons;

use App\Enums\CourseType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $video_url
 * @property mixed $provider
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
            'type' => CourseType::VIDEO->value,
            'video_url' => $this->video_url,
            'provider' => $this->provider,
        ];
    }
}
