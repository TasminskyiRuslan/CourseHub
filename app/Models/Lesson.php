<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property mixed $course
 */
class Lesson extends Model
{
    use HasSlug;

    protected $fillable = ['title', 'slug', 'position'];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Lesson $lesson) {
            if (!is_null($lesson->position)) {
                return;
            }

            $maxPosition = Lesson::where('course_id', $lesson->course_id)->max('position');

            $lesson->position = $maxPosition + 1;
        });
    }
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
    public function lessonable(): MorphTo
    {
        return $this->morphTo();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
