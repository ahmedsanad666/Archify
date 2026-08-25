<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'service_id',
        'interest_other',
        'message',
        'status',
        'internal_notes',
        'language_id',
        'ip_address',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeContacted(Builder $query): Builder
    {
        return $query->where('status', 'contacted');
    }
}
