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
        return response()->json([
            'stache' => $this->getStacheStats(),
            'cache' => $this->getApplicationCacheStats(),
            'static' => $this->getStaticCacheStats(),
            'images' => $this->getImageCacheStats(),
        ]);
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
                'stats' => $this->stats()->getData(true),
            ]);
        }

        $method = 'clear' . ucfirst($type) . 'Cache';

        if (! method_exists($this, $method)) {
            return response()->json(['error' => 'Invalid cache type'], 400);
        }

        $this->$method();

        return response()->json([
            'message' => ucfirst(__('admin-bar::strings.cache_cleared_successfully', ['type' => $type])),
            'stats' => $this->stats()->getData(true),
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
}
