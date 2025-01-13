<?php

namespace ElSchneider\StatamicAdminBar;

use Statamic\Providers\AddonServiceProvider;
use Illuminate\Support\Facades\Route;
use ElSchneider\StatamicAdminBar\Http\Controllers\AdminBarController;

class ServiceProvider extends AddonServiceProvider
{
    protected $publishables = [
        __DIR__.'/../resources/js' => 'js',
        __DIR__.'/../resources/css' => 'css',
    ];

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
    ];

    // protected $vite = [
    //     'input' => [
    //         'resources/js/addon.js',
    //     ],
    //     'publicDirectory' => 'resources/dist',
    // ];
    public function bootAddon()
    {

        $this->registerWebRoutes(function () {
            Route::get('/test', [AdminBarController::class, 'index']);
        });
    }
}
