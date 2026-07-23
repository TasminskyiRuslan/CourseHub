<?php

namespace App\Models;

use App\Enums\CourseType;
use App\Notifications\Course\CourseBannedNotification;
use App\Notifications\Course\CourseUnbannedNotification;
use App\Observers\Course\CourseObserver;
use Database\Factories\CourseFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $published_at
 * @property Carbon|null $banned_at
 * @property string|null $stripe_price_id
 * @property string|null $stripe_product_id
 * @property-read User|null $author
 * @property-read Collection<int, Lesson> $lessons
 * @property-read int|null $lessons_count
 * @property-read Collection<int, User> $students
 * @property-read int|null $students_count
 * @method static Builder<static>|Course active()
 * @method static CourseFactory factory($count = null, $state = [])
 * @method static Builder<static>|Course newModelQuery()
 * @method static Builder<static>|Course newQuery()
 * @method static Builder<static>|Course onlyTrashed()
 * @method static Builder<static>|Course query()
 * @method static Builder<static>|Course whereAuthorId($value)
 * @method static Builder<static>|Course whereBannedAt($value)
 * @method static Builder<static>|Course whereCreatedAt($value)
 * @method static Builder<static>|Course whereDeletedAt($value)
 * @method static Builder<static>|Course whereDescription($value)
 * @method static Builder<static>|Course whereId($value)
 * @method static Builder<static>|Course whereImagePath($value)
 * @method static Builder<static>|Course wherePrice($value)
 * @method static Builder<static>|Course wherePublishedAt($value)
 * @method static Builder<static>|Course whereSlug($value)
 * @method static Builder<static>|Course whereStripePriceId($value)
 * @method static Builder<static>|Course whereStripeProductId($value)
 * @method static Builder<static>|Course whereTitle($value)
 * @method static Builder<static>|Course whereType($value)
 * @method static Builder<static>|Course whereUpdatedAt($value)
 * @method static Builder<static>|Course withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Course withoutTrashed()
 * @mixin Eloquent
 */
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory, HasSlug, SoftDeletes;

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
        'stripe_product_id',
        'stripe_price_id',
        'type',
        'image_path',
        'published_at',
        'banned_at',
    ];

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
     * Get the options for generating the slug.
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
     * Get the route key name for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Send course banned notification.
     *
     * @return void
     */
    public function sendBanNotification(): void
    {
        $this->author->notify(new CourseBannedNotification($this));
    }

    /**
     * Send course unbanned notification.
     *
     * @return void
     */
    public function sendUnbanNotification(): void
    {
        $this->author->notify(new CourseUnbannedNotification($this));
    }

    /**
     * Get the students enrolled in the course.
     *
     * @return BelongsToMany<User, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('enrolled_at');
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
     * Check if the course is free or missing a Stripe price.
     *
     * @return bool
     */
    public function isFree(): bool
    {
        return (float)$this->price === 0.00;
    }

    /**
     * Check if the course is published.
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    /**
     * Publish the course.
     *
     * @return Course
     */
    public function publish(): static
    {
        $this->published_at = $this->freshTimestamp();
        return $this;
    }

    /**
     * Unpublish the course.
     *
     * @return Course
     */
    public function unpublish(): static
    {
        $this->published_at = null;
        return $this;
    }

    /**
     * Check if the course is banned.
     *
     * @return bool
     */
    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }

    /**
     * Ban the course.
     *
     * @return Course
     */
    public function ban(): static
    {
        $this->banned_at = $this->freshTimestamp();
        return $this;
    }

    /**
     * Unban the course.
     *
     * @return Course
     */
    public function unban(): static
    {
        $this->banned_at = null;
        return $this;
    }

    /**
     * Scope a query to only include active courses.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->whereNull('banned_at')
            ->whereHas('author', fn($q) => $q->whereNull('banned_at'));
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
            'published_at' => 'datetime',
            'banned_at' => 'datetime',
        ];
    }
}
