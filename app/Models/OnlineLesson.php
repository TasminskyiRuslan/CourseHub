<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class OnlineLesson extends Model
{
    protected $fillable = ['start_time', 'end_time', 'meeting_link'];

    public function lesson(): MorphOne
    {
        return $this->morphOne(Lesson::class, 'contentable');
    }
}
