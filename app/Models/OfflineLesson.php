<?php

namespace App\Models;

use App\Contracts\HasResource;
use App\Http\Resources\OfflineLessonResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\Resources\Json\JsonResource;

class OfflineLesson extends Model
{
    protected $fillable = ['start_time', 'end_time', 'address', 'room_number'];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }
    public function lesson(): MorphOne
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }

    public function toResource(?string $resourceClass = null): JsonResource
    {
        return $resourceClass
            ? new $resourceClass($this)
            : new OfflineLessonResource($this);
    }

}
