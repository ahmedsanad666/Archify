<?php

namespace App\Models;

use App\Scopes\OrderedScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faq extends Model
{
    protected $fillable = [
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new OrderedScope);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FaqTranslation::class);
    }

    /**
     * Translation for a given language id (or null if missing).
     */
    public function translationFor(int $languageId): ?FaqTranslation
    {
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('language_id', $languageId);
        }

        return $this->translations()->where('language_id', $languageId)->first();
    }
}
