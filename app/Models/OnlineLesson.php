<?php

namespace App\Models;

use App\Http\Resources\Api\Lesson\OnlineLessonResource;
use Database\Factories\OnlineLessonFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $start_time
 * @property Carbon|null $end_time
 * @property string|null $meeting_link
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Lesson|null $lesson
 * @method static OnlineLessonFactory factory($count = null, $state = [])
 * @method static Builder<static>|OnlineLesson newModelQuery()
 * @method static Builder<static>|OnlineLesson newQuery()
 * @method static Builder<static>|OnlineLesson onlyTrashed()
 * @method static Builder<static>|OnlineLesson query()
 * @method static Builder<static>|OnlineLesson whereCreatedAt($value)
 * @method static Builder<static>|OnlineLesson whereDeletedAt($value)
 * @method static Builder<static>|OnlineLesson whereEndTime($value)
 * @method static Builder<static>|OnlineLesson whereId($value)
 * @method static Builder<static>|OnlineLesson whereMeetingLink($value)
 * @method static Builder<static>|OnlineLesson whereStartTime($value)
 * @method static Builder<static>|OnlineLesson whereUpdatedAt($value)
 * @method static Builder<static>|OnlineLesson withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|OnlineLesson withoutTrashed()
 * @mixin Eloquent
 */
class OnlineLesson extends Model
{
    /** @use HasFactory<OnlineLessonFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'start_time',
        'end_time',
        'meeting_link'
    ];

    /**
     * Get the lesson associated with the online lesson.
     *
     * @return MorphOne
     */
    public function lesson(): MorphOne
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }

    /**
     * Transform the model into a JSON resource.
     *
     * @param string|null $resourceClass
     * @return JsonResource
     */
    public function toResource(?string $resourceClass = null): JsonResource
    {
        return $resourceClass
            ? new $resourceClass($this)
            : new OnlineLessonResource($this);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }
}
