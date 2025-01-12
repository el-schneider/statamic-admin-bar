<?php

namespace ElSchneider\StatamicAdminBar;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $publishables = [
        __DIR__.'/../resources/js' => 'js',
        __DIR__.'/../resources/css' => 'css',
    ];

    protected $vite = [
        'input' => [
            'resources/js/addon.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];
    public function bootAddon()
    {
    }
}
