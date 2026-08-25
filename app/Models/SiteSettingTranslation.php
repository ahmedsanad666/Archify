<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SiteSettingTranslation extends Model
{
    protected $fillable = [
        'site_setting_id',
        'language_id',
        'name',
        'slogan',
        'address',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    // relations 

    public function siteSetting(): BelongsTo
    {
        return $this->belongsTo(SiteSetting::class);
    }
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
