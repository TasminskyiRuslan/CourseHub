<?php

namespace App\Models;

use App\Enums\CourseType;
use App\Enums\UserPermission;
use App\Enums\UserRole;
use App\Observers\Course\CourseObserver;
use Database\Factories\CourseFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * @property int $author_id
 * @property string $title
 * @property string|null $slug
 * @property string|null $description
 * @property numeric $price
 * @property string|null $image_path
 * @property CourseType $type
 * @property bool $is_published
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\User $author
 * @property-read Collection<int, \App\Models\Lesson> $lessons
 * @property-read int|null $lessons_count
 * @method static \Database\Factories\CourseFactory factory($count = null, $state = [])
 * @method static Builder<static>|Course newModelQuery()
 * @method static Builder<static>|Course newQuery()
 * @method static Builder<static>|Course query()
 * @method static Builder<static>|Course visibleFor(?\App\Models\User $user)
 * @method static Builder<static>|Course whereAuthorId($value)
 * @method static Builder<static>|Course whereCreatedAt($value)
 * @method static Builder<static>|Course whereDescription($value)
 * @method static Builder<static>|Course whereId($value)
 * @method static Builder<static>|Course whereImagePath($value)
 * @method static Builder<static>|Course whereIsPublished($value)
 * @method static Builder<static>|Course wherePrice($value)
 * @method static Builder<static>|Course whereSlug($value)
 * @method static Builder<static>|Course whereTitle($value)
 * @method static Builder<static>|Course whereType($value)
 * @method static Builder<static>|Course whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory, HasSlug;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'price',
        'type',
        'image_path',
        'is_published'
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_published' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'type' => CourseType::class,
            'is_published' => 'boolean',
        ];
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::observe(CourseObserver::class);
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
     * Configure the slug generation options for the Author model.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Get the lessons for the course.
     *
     * @return HasMany
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * Get the author that owns the course.
     *
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Mark the course as published.
     *
     * @return Course
     */
    public function publish(): static
    {
        $this->is_published = true;
        return $this;
    }

    /**
     * Mark the course as unpublished.
     *
     * @return Course
     */
    public function unpublish(): static
    {
        $this->is_published = false;
        return $this;
    }

    /**
     * Scope a query to only include courses visible to the given user.
     *
     * @param Builder $query
     * @param  User|null  $user
     * @return Builder
     */
    public function scopeVisibleFor(Builder $query, ?User $user): Builder
    {
        if ($user?->hasPermissionTo(UserPermission::COURSE_VIEW_UNPUBLISHED->value) || $user?->hasRole(UserRole::SUPER_ADMIN->value)) {
            return $query;
        }
        return $query->where(function ($q) use ($user) {
            $q->where('is_published', true);
            if ($user) {
                $q->orWhere('author_id', $user->id);
            }
        });
    }

    /**
     * Set the image path.
     *
     * @return $this
     */
    public function setImage(string $path): static
    {
        $this->image_path = $path;
        return $this;
    }

    /**
     * Remove the image path.
     *
     * @return $this
     */
    public function removeImage(): static
    {
        $this->image_path = null;
        return $this;
    }
}
