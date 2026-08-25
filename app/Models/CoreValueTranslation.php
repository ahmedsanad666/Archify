<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoreValueTranslation extends Model
{
    protected $fillable = [
        'core_value_id',
        'language_id',
        'title',
        'short_description',
    ];

    public function coreValue(): BelongsTo
    {
        return $this->belongsTo(CoreValue::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
