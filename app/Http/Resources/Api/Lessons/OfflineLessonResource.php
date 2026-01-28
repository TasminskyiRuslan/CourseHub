<?php

namespace App\Http\Resources\Api\Lessons;

use App\Enums\CourseType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $start_time
 * @property mixed $end_time
 * @property mixed $address
 * @property mixed $room_number
 */
class OfflineLessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => CourseType::OFFLINE->value,
            'start_time' => $this->start_time?->toIso8601String(),
            'end_time' => $this->end_time?->toIso8601String(),
            'address' => $this->address,
            'room_number' => $this->room_number,
        ];
    }
}
