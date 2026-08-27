<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $seo = $seo ?? app(\App\Services\SiteSettingService::class)->documentSeo();
            $seoTitle = $seo['title'] ?? config('app.name', 'Archify');
            $seoDescription = $seo['description'] ?? '';
            $seoKeywords = $seo['keywords'] ?? '';
            $seoOgImage = $seo['og_image'] ?? '';
            $seoFavicon = $seo['favicon'] ?? '';
        @endphp

        <title inertia>{{ $seoTitle }}</title>

        @if ($seoDescription !== '')
            <meta inertia="description" name="description" content="{{ $seoDescription }}">
            <meta inertia="og:description" property="og:description" content="{{ $seoDescription }}">
        @endif

        @if ($seoKeywords !== '')
            <meta inertia="keywords" name="keywords" content="{{ $seoKeywords }}">
        @endif

        <meta inertia="og:title" property="og:title" content="{{ $seoTitle }}">
        <meta inertia="og:type" property="og:type" content="website">

        @if ($seoOgImage !== '')
            <meta inertia="og:image" property="og:image" content="{{ $seoOgImage }}">
        @endif

        @if ($seoFavicon !== '')
            <link inertia="icon" rel="icon" href="{{ $seoFavicon }}">
        @endif

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
