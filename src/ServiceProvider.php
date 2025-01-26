<?php

namespace ElSchneider\StatamicAdminBar;

use ElSchneider\StatamicAdminBar\Http\Controllers\AdminBarController;
use Illuminate\Support\Facades\Route;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'actions' => __DIR__ . '/../routes/actions.php',
    ];

    protected $vite = [
        'input' => [
            'resources/js/admin-bar.ts',
            'resources/css/admin-bar.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function bootAddon()
    {

        $this->registerWebRoutes(function () {
            Route::get('test', AdminBarController::class);
        });
    }
}
