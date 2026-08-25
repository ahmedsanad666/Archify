<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AboutPage extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * Singleton table name (singular per project brief).
     */
    protected $table = 'about_page';

    protected $fillable = [];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('story_image')->singleFile();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(AboutPageTranslation::class);
    }

    /**
     * Translation for a given language id (or null if missing).
     */
    public function translationFor(int $languageId): ?AboutPageTranslation
    {
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('language_id', $languageId);
        }

        return $this->translations()->where('language_id', $languageId)->first();
    }
}
