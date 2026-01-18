<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class OfflineLesson extends Model
{
    protected $fillable = ['start_time', 'end_time', 'address', 'room_number'];

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
}
