<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class StatisticTranslation extends Model
{
    protected $fillable = [
        'statistic_id',
        'language_id',
        'label',
    ];

    public function statistic(): BelongsTo
    {
        return $this->belongsTo(Statistic::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
