<?php

namespace ElSchneider\StatamicAdminBar\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use League\Glide\Server;
use Statamic\Facades\Stache;
use Statamic\Facades\StaticCache;
use Statamic\Http\Controllers\Controller;
use Statamic\Support\Str;

class CacheController extends Controller
{
    public function stats(): JsonResponse
    {
        return response()->json($this->formattedStats());
    }

    protected function getStacheStats(): array
    {
        $size = Stache::fileSize();
        $time = Stache::buildTime();
        $built = Stache::buildDate();

        return [
            'records' => Stache::fileCount(),
            'size' => $size ? Str::fileSizeForHumans($size) : null,
            'time' => $time ? Str::timeForHumans($time) : __('Refresh'),
            'rebuilt' => $built ? $built->diffForHumans() : __('Refresh'),
        ];
    }

    protected function getApplicationCacheStats(): array
    {
        $driver = config('cache.default');
        $driver = ($driver === 'statamic') ? 'file (statamic)' : $driver;

        return compact('driver');
    }

    protected function getImageCacheStats(): array
    {
        $files = collect(app(Server::class)->getCache()->listContents('', true))
            ->filter(function ($file) {
                return $file['type'] === 'file';
            });

        return [
            'count' => $files->count(),
            'size' => Str::fileSizeForHumans($files->reduce(function ($size, $file) {
                return $size + $file->fileSize();
            }, 0)),
        ];
    }

    protected function getStaticCacheStats(): array
    {
        $strategy = config('statamic.static_caching.strategy');

        return [
            'enabled' => (bool) $strategy,
            'strategy' => $strategy ?? __('Disabled'),
            'count' => StaticCache::driver()->getUrls()->count(),
        ];
    }

    public function clear(Request $request, string $type): JsonResponse
    {
        if ($type === 'all') {
            $this->clearStacheCache();
            $this->clearStaticCache();
            $this->clearApplicationCache();
            $this->clearImageCache();

            return response()->json([
                'message' => __('admin-bar::strings.all_caches_cleared_successfully'),
                'stats' => $this->formattedStats(),
            ]);
        }

        if ($type === 'static' && $request->has('url')) {

            $cache = app(\Statamic\StaticCaching\Cacher::class);

            $cache->invalidateUrl($request->get('url'));

            return response()->json([
                'message' => __('admin-bar::strings.static_cache_cleared_for_url', ['url' => $request->get('url')]),
                'stats' => $this->formattedStats(),
            ]);
        }

        $method = 'clear' . ucfirst($type) . 'Cache';

        if (! method_exists($this, $method)) {
            return response()->json(['error' => 'Invalid cache type'], 400);
        }

        $this->$method();

        return response()->json([
            'message' => ucfirst(__('admin-bar::strings.cache_cleared_successfully', ['type' => $type])),
            'stats' => $this->formattedStats(),
        ]);
    }

    protected function clearStacheCache(): void
    {
        Stache::refresh();
    }

    protected function clearStaticCache(): void
    {
        StaticCache::flush();
    }

    protected function clearApplicationCache(): void
    {
        Artisan::call('cache:clear');
    }

    protected function clearImageCache(): void
    {
        Artisan::call('statamic:glide:clear');
    }

    public function formattedStats(): array
    {
        $stache = $this->getStacheStats();
        $static = $this->getStaticCacheStats();
        $images = $this->getImageCacheStats();
        $appCache = $this->getApplicationCacheStats();

        return [
            'stache' => trans_choice('admin-bar::strings.stache_cache_stats', $stache['records'], [
                'records' => $stache['records'],
                'size' => $stache['size'] ?? '0 B',
            ]),
            'static' => trans_choice('admin-bar::strings.static_cache_stats', $static['count'], [
                'count' => $static['count'],
            ]),
            'images' => trans_choice('admin-bar::strings.image_cache_stats', $images['count'], [
                'count' => $images['count'],
                'size' => $images['size'] ?? '0 B',
            ]),
            'cache' => __('admin-bar::strings.application_cache_stats', [
                'driver' => ucfirst($appCache['driver']),
            ]),
        ];
    }
}
