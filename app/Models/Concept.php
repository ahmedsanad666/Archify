<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Concept extends Model
{
    /** @use HasFactory<\Database\Factories\ConceptFactory> */
    use HasFactory;
    protected $fillable = [
        'icon',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(ConceptTranslation::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_concept');
    }

    /**
     * Translation for a given language id (or null if missing).
     */
    public function translationFor(int $languageId): ?ConceptTranslation
    {
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('language_id', $languageId);
        }

        return $this->translations()->where('language_id', $languageId)->first();
    }
}
