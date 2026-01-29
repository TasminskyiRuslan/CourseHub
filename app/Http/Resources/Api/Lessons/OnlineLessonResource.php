<?php

namespace App\Http\Resources\Api\Lessons;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $start_time
 * @property mixed $end_time
 * @property mixed $meeting_link
 */
class OnlineLessonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'start_time' => $this->start_time?->toIso8601String(),
            'end_time' => $this->end_time?->toIso8601String(),
            'meeting_link' => $this->meeting_link,
        ];
    }
}
