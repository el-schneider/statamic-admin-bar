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
        $vite = (new Vite())->useHotfile(__DIR__ . '/../../resources/dist/hot')->useBuildDirectory('vendor/statamic-admin-bar/build');
        $assets = sprintf('<script type="module" src="%s"></script>', $vite->asset('resources/js/admin-bar.js'));
        $styles = sprintf('<link rel="stylesheet" href="%s">', $vite->asset('resources/css/admin-bar.css'));
        return $assets . $styles . '<div id="admin-bar"></div>';
    }
}
