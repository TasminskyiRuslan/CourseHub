<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Lesson extends Model
{
    protected $fillable = ['course_id', 'title', 'position'];

    public function contentable(): MorphTo
    {
        return $this->morphTo();
    }
}
