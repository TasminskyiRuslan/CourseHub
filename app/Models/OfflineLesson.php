<?php

namespace App\Models;

use App\Http\Resources\Api\Lessons\OfflineLessonResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property string|null $address
 * @property string|null $room_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Lesson|null $lesson
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfflineLesson newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfflineLesson newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfflineLesson query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfflineLesson whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfflineLesson whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfflineLesson whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfflineLesson whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfflineLesson whereRoomNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfflineLesson whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfflineLesson whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OfflineLesson extends Model
{
    protected $fillable = [
        'start_time',
        'end_time',
        'address',
        'room_number'
    ];

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
