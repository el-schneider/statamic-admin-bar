<?php

namespace ElSchneider\StatamicAdminBar;

use ElSchneider\StatamicAdminBar\Http\Controllers\AdminBarController;
use Illuminate\Support\Facades\Route;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $publishables = [
        __DIR__ . '/../resources/dist' => '',
    ];

    protected $routes = [
        'actions' => __DIR__ . '/../routes/actions.php',
    ];

    public function bootAddon()
    {

        $this->registerWebRoutes(function () {
            Route::get('test', AdminBarController::class);
        });
    }
}
