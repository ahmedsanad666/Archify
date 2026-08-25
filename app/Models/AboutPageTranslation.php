<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AboutPageTranslation extends Model
{
    protected $fillable = [
        'about_page_id',
        'language_id',
        'story_title',
        'story_description',
        'vision_title',
        'vision_description',
        'mission_title',
        'mission_description',
    ];

    public function aboutPage(): BelongsTo
    {
        return $this->belongsTo(AboutPage::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
