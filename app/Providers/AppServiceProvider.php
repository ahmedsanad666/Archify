<?php

namespace App\Providers;

use App\Services\SiteSettingService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Route::bind('medium', function (string $value) {
            return Media::query()->findOrFail($value);
        });

        View::composer('app', function ($view) {
            if ($view->offsetExists('seo')) {
                return;
            }

            $view->with('seo', app(SiteSettingService::class)->documentSeo());
        });
    }
}
