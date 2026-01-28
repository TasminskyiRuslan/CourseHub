<?php

namespace App\Models;

use App\Enums\CourseType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property mixed|null $type
 * @property mixed $user_id
 * @property mixed $image_path
 * @property mixed $id
 * @method where(string $string, true $true)
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
