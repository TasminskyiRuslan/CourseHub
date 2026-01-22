<?php

namespace App\Models;

use App\Contracts\HasResource;
use App\Http\Resources\VideoLessonResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoLesson extends Model
{
    protected $fillable = ['video_url', 'provider'];

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
