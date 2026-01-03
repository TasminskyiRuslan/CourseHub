<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class OfflineLesson extends Model
{
    protected $fillable = ['start_time', 'end_time', 'address', 'room_number'];

    public function lesson(): MorphOne
    {
        return $this->morphOne(Lesson::class, 'contentable');
    }
}
