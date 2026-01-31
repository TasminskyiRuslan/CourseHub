<?php

namespace App\Models;

use App\Enums\CourseType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property mixed $id
 * @property mixed|null $type
 * @property mixed $image_path
 * @method where(string $string, true $true)
 * @property int $author_id
 * @property string $title
 * @property string|null $slug
 * @property string|null $description
 * @property numeric $price
 * @property bool $is_published
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $author
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lesson> $lessons
 * @property-read int|null $lessons_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Course extends Model
{
    use HasSlug;

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'description',
        'price',
        'type',
        'image_path',
        'is_published'
    ];

    protected $attributes = [
        'is_published' => false,
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'type' => CourseType::class,
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Course $course) {
            $course->lessons->each->delete();
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
            ->doNotGenerateSlugsOnUpdate();
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function isVisibleFor(?User $user): bool
    {
        return $this->is_published || ($user && ($user->isAdmin() || $user->isAuthorOf($this)));
    }

    public function publish(): void
    {
        $this->is_published = true;
    }

    public function unpublish(): void
    {
        $this->is_published = false;
    }
}
