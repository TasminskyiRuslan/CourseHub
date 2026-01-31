<?php

namespace App\Models;

use App\Http\Resources\Api\Lessons\VideoLessonResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string|null $video_url
 * @property string|null $provider
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Lesson|null $lesson
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLesson newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLesson newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLesson query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLesson whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLesson whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLesson whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLesson whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLesson whereVideoUrl($value)
 * @mixin \Eloquent
 */
class VideoLesson extends Model
{
    protected $fillable = [
        'video_url',
        'provider'
    ];

    public function lesson(): MorphOne
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }

    public function toResource(?string $resourceClass = null): JsonResource
    {
        return $resourceClass
            ? new $resourceClass($this)
            : new VideoLessonResource($this);
    }
}
