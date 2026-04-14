<?php

namespace App\Models;

use App\Http\Resources\Api\Lessons\OfflineLessonResource;
use Database\Factories\OfflineLessonFactory;
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
 * @property string|null $address
 * @property string|null $room_number
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Lesson|null $lesson
 * @method static OfflineLessonFactory factory($count = null, $state = [])
 * @method static Builder<static>|OfflineLesson newModelQuery()
 * @method static Builder<static>|OfflineLesson newQuery()
 * @method static Builder<static>|OfflineLesson query()
 * @method static Builder<static>|OfflineLesson whereAddress($value)
 * @method static Builder<static>|OfflineLesson whereCreatedAt($value)
 * @method static Builder<static>|OfflineLesson whereEndTime($value)
 * @method static Builder<static>|OfflineLesson whereId($value)
 * @method static Builder<static>|OfflineLesson whereRoomNumber($value)
 * @method static Builder<static>|OfflineLesson whereStartTime($value)
 * @method static Builder<static>|OfflineLesson whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OfflineLesson extends Model
{
    /** @use HasFactory<OfflineLessonFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'start_time',
        'end_time',
        'address',
        'room_number'
    ];

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

    /**
     * Get the lesson associated with the offline lesson.
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
            : new OfflineLessonResource($this);
    }
}
