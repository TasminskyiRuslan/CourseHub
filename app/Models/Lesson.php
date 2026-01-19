<?php

namespace App\Models;

use App\DTO\CourseFilterDTO;
use App\Enums\CourseSortField;
use App\Enums\CourseType;
use App\Enums\SortOrder;
use Illuminate\Database\Eloquent\Builder;
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
}
