<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SiteSetting extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'email',
        'phone',
        'whatsapp',
        'map_lat',
        'map_lng',
        'instagram_url',
        'youtube_url',
        'twitter_url',
        'google_analytics_id',
        'gtm_id',
        'facebook_pixel_id',
        'google_site_verification',
        'robots_txt',
        'auto_translate_enabled',
    ];

    protected $casts = [
        'auto_translate_enabled' => 'boolean',
        'map_lat' => 'decimal:7',
        'map_lng' => 'decimal:7',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('favicon')->singleFile();
        $this->addMediaCollection('og_image')->singleFile();
        $this->addMediaCollection('banner_about')->singleFile();
        $this->addMediaCollection('banner_services')->singleFile();
        $this->addMediaCollection('banner_projects')->singleFile();
        $this->addMediaCollection('banner_blogs')->singleFile();
        $this->addMediaCollection('banner_contact')->singleFile();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SiteSettingTranslation::class);
    }

    /**
     * Translation for a given language id (or null if missing).
     */
    public function translationFor(int $languageId): ?SiteSettingTranslation
    {
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('language_id', $languageId);
        }

        return $this->translations()->where('language_id', $languageId)->first();
    }

    /**
     * Sensible robots.txt for Archify public site + admin CMS.
     * Blocks private admin/auth paths and direct storage/API while allowing the rest.
     */
    public static function defaultRobotsTxt(): string
    {
        return implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /storage/',
            'Disallow: /api/',
        ]);
    }

    public function resolvedRobotsTxt(): string
    {
        $value = is_string($this->robots_txt) ? trim($this->robots_txt) : '';

        return $value !== '' ? $this->robots_txt : static::defaultRobotsTxt();
    }
}
