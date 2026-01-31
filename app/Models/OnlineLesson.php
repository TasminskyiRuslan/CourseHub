<?php

namespace App\Models;

use App\Http\Resources\Api\Lessons\OnlineLessonResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property string|null $meeting_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Lesson|null $lesson
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OnlineLesson newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OnlineLesson newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OnlineLesson query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OnlineLesson whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OnlineLesson whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OnlineLesson whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OnlineLesson whereMeetingLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OnlineLesson whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OnlineLesson whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OnlineLesson extends Model
{
    protected $fillable = [
        'start_time',
        'end_time',
        'meeting_link'
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
            : new OnlineLessonResource($this);
    }
}
