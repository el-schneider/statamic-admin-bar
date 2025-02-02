<?php

namespace ElSchneider\StatamicAdminBar;

use ElSchneider\StatamicAdminBar\Http\Controllers\AdminBarController;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\Permission;
use Statamic\Facades\Preference;
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

        Permission::extend(function () {
            Permission::group('admin_bar', 'Admin Bar', function () {
                Permission::register('view admin bar')
                    ->label('View Admin Bar');
            });
        });

        Preference::extend(fn ($preference) => [
            'general' => [
                'display' => __('Admin Bar'),
                'fields' => [
                    'admin_bar' => [
                        'type' => 'section',
                        'display' => __('Admin Bar'),
                    ],
                    'admin_bar_enabled' => [
                        'type' => 'toggle',
                        'display' => __('Enabled'),
                        'width' => '25',
                        'default' => true,
                    ],
                    'admin_bar_dark_mode' => [
                        'type' => 'select',
                        'instructions' => __("`auto` will use the CP's dark mode setting."),
                        'display' => __('Dark Mode'),
                        'width' => '25',
                        'default' => 'auto',
                        'options' => [
                            'auto' => __('Auto'),
                            'light' => __('Light'),
                            'dark' => __('Dark'),
                        ],
                    ],
                ],
            ],
        ]);

        $this->registerWebRoutes(function () {
            Route::get('test', AdminBarController::class);
        });
    }
}
