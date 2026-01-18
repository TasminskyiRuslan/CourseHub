<?php

namespace App\Http\Resources;

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
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'meeting_link' => $this->meeting_link,
        ];
    }
}
