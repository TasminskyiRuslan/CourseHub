<?php

namespace App\Models;

use App\Observers\Lesson\LessonObserver;
use Database\Factories\LessonFactory;
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
 * @method static LessonFactory factory($count = null, $state = [])
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
    /** @use HasFactory<LessonFactory> */
    use HasSlug, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'position'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::observe(LessonObserver::class);
    }

    /**
     * Get the route key name for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->extraScope(fn($builder) => $builder->where('course_id', $this->course_id));
    }

    /**
     * Get the owning lessonable model.
     *
     * @return MorphTo
     */
    public function lessonable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the course that owns the lesson.
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Scope a query to only include lessons of courses visible to the given user.
     *
     * @param Builder $query
     * @param User|null $user
     * @return Builder
     */
    public function scopeVisibleFor(Builder $query, ?User $user): Builder
    {
        return $query->whereHas('course', function (Builder $q) use ($user) {
            $q->visibleFor($user);
        });
    }
}
