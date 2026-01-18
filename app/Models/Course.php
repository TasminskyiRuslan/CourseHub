<?php

namespace App\Models;

use App\DTO\CourseFilterDTO;
use App\Enums\CourseSortField;
use App\Enums\CourseType;
use App\Enums\SortOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property mixed|null $type
 * @property mixed $user_id
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
        'image_url',
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

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopeFilter(Builder $query, CourseFilterDTO $filters): void
    {
        $query
            ->when($filters->type, fn(Builder $q, CourseType $type) => $q->where('type', $type))
            ->when($filters->search, function (Builder $q, string $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters->author, function (Builder $q, string $slug) {
                $q->whereHas('author', function (Builder $sub) use ($slug) {
                    $sub->where('slug', $slug);
                });
            });
    }

    public function scopeSort(Builder $query, CourseFilterDTO $filters): void
    {
        if ($filters->sort instanceof CourseSortField) {
            $query->orderBy(
                $filters->sort->value,
                $filters->order?->value ?? SortOrder::ASC->value
            );
        } else {
            $query->latest();
        }
    }

    public function isVisibleFor(?User $user): bool
    {
        return $this->is_published || ($user && ($user->isAdmin() || $user->isOwnerOf($this)));
    }

//    public function isPublished(): bool
//    {
//        return $this->is_published;
//    }
}
