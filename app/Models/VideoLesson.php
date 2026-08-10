<?php

declare(strict_types=1);

namespace App\Models;

use App\Http\Resources\Api\Lesson\VideoLessonResource;
use Database\Factories\VideoLessonFactory;
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
 * @property string|null $video_url
 * @property string|null $provider
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read \App\Models\Lesson|null $lesson
 *
 * @method static \Database\Factories\VideoLessonFactory factory($count = null, $state = [])
 * @method static Builder<static>|VideoLesson newModelQuery()
 * @method static Builder<static>|VideoLesson newQuery()
 * @method static Builder<static>|VideoLesson onlyTrashed()
 * @method static Builder<static>|VideoLesson query()
 * @method static Builder<static>|VideoLesson whereCreatedAt($value)
 * @method static Builder<static>|VideoLesson whereDeletedAt($value)
 * @method static Builder<static>|VideoLesson whereId($value)
 * @method static Builder<static>|VideoLesson whereProvider($value)
 * @method static Builder<static>|VideoLesson whereUpdatedAt($value)
 * @method static Builder<static>|VideoLesson whereVideoUrl($value)
 * @method static Builder<static>|VideoLesson withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|VideoLesson withoutTrashed()
 *
 * @mixin Eloquent
 */
class VideoLesson extends Model
{
    /** @use HasFactory<VideoLessonFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'video_url',
        'provider',
    ];

    /**
     * Get the lesson associated with the video lesson.
     */
    public function lesson(): MorphOne
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }

    /**
     * Transform the model into a JSON resource.
     */
    public function toResource(?string $resourceClass = null): JsonResource
    {
        return $resourceClass
            ? new $resourceClass($this)
            : new VideoLessonResource($this);
    }
}
