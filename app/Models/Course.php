<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Course extends Model
{
    use HasSlug;
    protected $fillable = [
        'user_id', 'title', 'slug', 'description',
        'price', 'type', 'image_url', 'is_published'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
