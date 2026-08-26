<?php

namespace Database\Seeders\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

trait AttachesRemoteMedia
{
    protected function attachRemoteImage(Model $model, string $url, string $collection): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'seed_img_');

        if ($tmp === false) {
            Log::warning(static::class.': unable to create temp file', ['url' => $url]);

            return;
        }

        $path = $tmp.'.jpg';
        @rename($tmp, $path);

        try {
            $response = Http::timeout(45)
                ->withHeaders(['User-Agent' => 'ArchifyContentSeeder/1.0'])
                ->sink($path)
                ->get($url);

            if (! $response->successful() || ! is_file($path) || filesize($path) < 1000) {
                Log::warning(static::class.': image download failed', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                return;
            }

            $model->addMedia($path)
                ->usingFileName(Str::slug(pathinfo(parse_url($url, PHP_URL_PATH) ?: 'image', PATHINFO_FILENAME)).'.jpg')
                ->toMediaCollection($collection);
        } catch (Throwable $e) {
            Log::warning(static::class.': '.$e->getMessage(), ['url' => $url]);
        } finally {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * @param  list<string>  $urls
     */
    protected function syncRemoteImages(Model $model, string $collection, array $urls): void
    {
        $model->clearMediaCollection($collection);

        foreach ($urls as $url) {
            $this->attachRemoteImage($model, $url, $collection);
        }
    }
}
