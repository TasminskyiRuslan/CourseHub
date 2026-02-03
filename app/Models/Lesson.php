<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * @property int $course_id
 * @property string $title
 * @property string|null $slug
 * @property int $position
 * @property string $lessonable_type
 * @property int $lessonable_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Course $course
 * @property-read Model|Eloquent $lessonable
 * @method static Builder<static>|Lesson newModelQuery()
 * @method static Builder<static>|Lesson newQuery()
 * @method static Builder<static>|Lesson query()
 * @method static Builder<static>|Lesson whereCourseId($value)
 * @method static Builder<static>|Lesson whereCreatedAt($value)
 * @method static Builder<static>|Lesson whereId($value)
 * @method static Builder<static>|Lesson whereLessonableId($value)
 * @method static Builder<static>|Lesson whereLessonableType($value)
 * @method static Builder<static>|Lesson wherePosition($value)
 * @method static Builder<static>|Lesson whereSlug($value)
 * @method static Builder<static>|Lesson whereTitle($value)
 * @method static Builder<static>|Lesson whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Lesson extends Model
{
    use HasSlug, HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'position'
    ];

    protected static function booted(): void
    {
        static::creating(function (Lesson $lesson) {
            if (!is_null($lesson->position)) {
                return;
            }
            $maxPosition = Lesson::where('course_id', $lesson->course_id)->max('position');
            $lesson->position = $maxPosition + 1;
        });
        static::deleting(function (Lesson $lesson) {
            $lesson->lessonable?->delete();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->extraScope(fn($builder) => $builder->where('course_id', $this->course_id));
    }

    public function lessonable(): MorphTo
    {
        return $this->morphTo();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }
}
