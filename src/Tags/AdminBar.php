<?php

namespace ElSchneider\StatamicAdminBar\Tags;

use ElSchneider\StatamicAdminBar\Http\Controllers\AccessController;
use Illuminate\Foundation\Vite;
use Statamic\Facades\Preference;
use Statamic\StaticCaching\Cacher;
use Statamic\Tags\Tags;

class AdminBar extends Tags
{
    protected AccessController $authController;

    protected Cacher $cacher;

    public function __construct(AccessController $authController, Cacher $cacher)
    {
        $this->authController = $authController;
        $this->cacher = $cacher;
    }

    /**
     * The {{ admin_bar }} tag.
     */
    public function index(): string
    {
        // Don't render anything if static caching is disabled and user can't view admin bar
        if ($this->isStaticCachingDisabled() && ! $this->authController->hasAdminBarAccess()) {
            return '';
        }

        // Don't render anything if running the `statamic:ssg:generate` command
        if ($this->isSSG()) {
            return '';
        }

        $adminBarHeight = '36px';

        // Those are the main assets, that are always applied
        $vite = (new Vite)->useHotfile(__DIR__ . '/../../resources/dist/hot')->useBuildDirectory('vendor/statamic-admin-bar/build');
        $assets = sprintf('<script type="module" src="%s"></script>', $vite->asset('resources/js/admin-bar.ts'));
        $styles = sprintf('<link rel="stylesheet" href="%s">', $vite->asset('resources/css/admin-bar.css'));

        // The root element, into which the admin bar is rendered
        $rootClass = $this->canViewAndCachingDisabled() && $this->isDarkmode() ? 'dark' : '';
        $root = <<<HTML
            <div
                id="admin-bar"
                data-admin-bar-height="{$adminBarHeight}"
                class="{$rootClass}"
            >
                <div></div>
            </div>
        HTML;

        $inlineScripts = '';

        // Those are the base styles, that are always applied
        $inlineStyles = <<<'HTML'
            <style>
                #admin-bar {
                    position: fixed;
                    z-index: 99999;
                }

                @media (min-width: 768px) {
                    #admin-bar {
                        height: var(--admin-bar-height);
                        position: relative;
                    }
                }
            </style>
        HTML;

        if ($this->canViewAndCachingDisabled()) {
            //  Don't wait for the vue component to initialize if we already know everything

            $inlineStyles .= <<<HTML
                <style>
                    #admin-bar {
                        display: block;
                    }

                    :root {
                        --admin-bar-height: {$adminBarHeight};
                    }
                </style>
            HTML;
        }

        if (! $this->canViewAndCachingDisabled()) {

            // hide by default, as we don't know if we can show it yet
            $inlineStyles .= <<<'HTML'
                <style>
                    #admin-bar {
                        display: none;
                    }
                </style>
            HTML;

            // If static caching is enabled and we have used admin-bar before,
            // we can immediately show it, to avoid layout shifts
            $inlineScripts .= <<<'HTML'
                <script>
                    // Immediately check localStorage and show admin bar if user preference exists
                    const adminBarHeight = localStorage.getItem('admin-bar-height')

                    const adminBar = document.getElementById('admin-bar')

                    if (adminBarHeight) {
                        document.documentElement.style.setProperty('--admin-bar-height', adminBarHeight);
                        adminBar.style.display = 'block';
                    }

                    const preferences = JSON.parse(localStorage.getItem('statamic-admin-bar-preferences'));

                    if (preferences) {
                        adminBar.classList.add(preferences.dark_mode ? 'dark' : '');
                    }
                </script>
            HTML;
        }

        return $assets . $styles . $inlineStyles . $root . $inlineScripts;
    }

    private function canViewAndCachingDisabled(): bool
    {
        return $this->isStaticCachingDisabled() && $this->authController->canViewAdminBar();
    }

    private function isDarkmode()
    {
        $statamic_theme = Preference::get('theme', 'dark');
        $dark_mode = Preference::get('admin_bar_dark_mode', $statamic_theme);

        return $dark_mode === 'dark';
    }

    private function isStaticCachingDisabled(): bool
    {
        $strategy = config('statamic.static_caching.strategy');

        return empty($strategy) || $strategy === 'null';
    }

    private function isSSG(): bool
    {
        if (! isset($_SERVER['argv']) || count($_SERVER['argv']) < 2) {
            return false;
        }

        return in_array($_SERVER['argv'][1], ['ssg:generate', 'statamic:ssg:generate']);
    }
}
