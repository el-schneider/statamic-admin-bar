<?php

namespace ElSchneider\StatamicAdminBar\Tags;

use Illuminate\Foundation\Vite;
use Statamic\Tags\Tags;

class AdminBar extends Tags
{
    /**
     * The {{ admin_bar }} tag.
     *
     * @return string
     */
    public function index()
    {
        $vite = (new Vite)->useHotfile(__DIR__ . '/../../resources/dist/hot')->useBuildDirectory('vendor/statamic-admin-bar/build');
        $assets = sprintf('<script type="module" src="%s"></script>', $vite->asset('resources/js/admin-bar.ts'));
        $styles = sprintf('<link rel="stylesheet" href="%s">', $vite->asset('resources/css/admin-bar.css'));
        $inlineStyles = <<<'HTML'
            <style>
                #admin-bar {
                    display: none;
                    position: fixed;
                    z-index: 99999;
                }

                @media (min-width: 768px) {
                    #admin-bar {
                        height: 2rem;
                        position: relative;
                    }
                }
            </style>
        HTML;

        $inlineScripts = <<<'HTML'
            <script>
                // Immediately check localStorage and show admin bar if user preference exists
                if (localStorage.getItem('admin-bar-user') === 'true') {
                    document.getElementById('admin-bar').style.display = 'block';
                }
            </script>
        HTML;

        return $assets . $styles . $inlineStyles . '<div id="admin-bar"></div>' . $inlineScripts;
    }
}
