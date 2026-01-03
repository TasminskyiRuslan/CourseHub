<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class VideoLesson extends Model
{
    protected $fillable = ['video_url'];

    public function lesson(): MorphOne
    {
        return $this->morphOne(Lesson::class, 'contentable');
    }
}
