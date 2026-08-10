<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\Lesson;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Carbon $start_time
 * @property-read Carbon $end_time
 * @property-read string $address
 * @property-read string|null $room_number
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
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'address' => $this->address,
            'room_number' => $this->room_number,
        ];
    }
}
