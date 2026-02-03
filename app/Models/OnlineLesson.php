<?php

namespace App\Models;

use App\Http\Resources\Api\Lessons\OnlineLessonResource;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $start_time
 * @property Carbon|null $end_time
 * @property string|null $meeting_link
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Lesson|null $lesson
 * @method static Builder<static>|OnlineLesson newModelQuery()
 * @method static Builder<static>|OnlineLesson newQuery()
 * @method static Builder<static>|OnlineLesson query()
 * @method static Builder<static>|OnlineLesson whereCreatedAt($value)
 * @method static Builder<static>|OnlineLesson whereEndTime($value)
 * @method static Builder<static>|OnlineLesson whereId($value)
 * @method static Builder<static>|OnlineLesson whereMeetingLink($value)
 * @method static Builder<static>|OnlineLesson whereStartTime($value)
 * @method static Builder<static>|OnlineLesson whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OnlineLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'meeting_link'
    ];

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

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }
}
