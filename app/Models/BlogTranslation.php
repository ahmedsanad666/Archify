<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogTranslation extends Model
{
    protected $fillable = [
        'blog_id',
        'language_id',
        'title',
        'slug',
        'content',
        'read_time',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'translation_status',
    ];

    protected $casts = [
        'read_time' => 'integer',
    ];

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
