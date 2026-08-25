<?php

namespace App\Models;

use App\Scopes\OrderedScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TeamMember extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\TeamMemberFactory> */
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'order',
        'linkedin_url',
        'behance_url',
        'instagram_url',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new OrderedScope);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TeamMemberTranslation::class);
    }

    /**
     * Translation for a given language id (or null if missing).
     */
    public function translationFor(int $languageId): ?TeamMemberTranslation
    {
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('language_id', $languageId);
        }

        return $this->translations()->where('language_id', $languageId)->first();
    }
}
