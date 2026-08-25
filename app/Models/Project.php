<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Project extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'project_category_id',
        'client_name',
        'location',
        'year',
        'video_url',
        'views_count',
    ];

    protected $casts = [
        'year' => 'integer',
        'views_count' => 'integer',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')->singleFile();
        $this->addMediaCollection('preview_video')->singleFile();
        $this->addMediaCollection('images_2d');
        $this->addMediaCollection('images_3d');
        $this->addMediaCollection('images_outdoor');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProjectTranslation::class);
    }

    public function concepts(): BelongsToMany
    {
        return $this->belongsToMany(Concept::class, 'project_concept');
    }

    /**
     * Translation for a given language id (or null if missing).
     */
    public function translationFor(int $languageId): ?ProjectTranslation
    {
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('language_id', $languageId);
        }

        return $this->translations()->where('language_id', $languageId)->first();
    }
}
