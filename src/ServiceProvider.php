<?php

namespace ElSchneider\StatamicAdminBar;

use ElSchneider\StatamicAdminBar\Http\Controllers\AdminBarController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
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

    protected $listen = [
        'Illuminate/Auth/Events/Login' => ['ElSchneider\StatamicAdminBar\Listeners\LoginListener'],
    ];

    protected $middlewareGroups = [
        'web' => [
            \ElSchneider\StatamicAdminBar\Http\Middleware\SetAdminBarLocale::class,
        ],
    ];

    public function bootAddon()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'admin-bar');

        Str::macro('initials', function (string $string): string {
            $string = trim($string);
            if (empty($string)) {
                return 'AA';
            }

            // Get first letter
            $initials = strtoupper($string[0]);

            // Try to get second letter from word boundaries
            preg_match_all('/\s+(\w)/', $string, $matches);
            if (! empty($matches[1])) {
                $initials .= strtoupper($matches[1][0]);
            } else {
                // If no word boundaries, either use second letter or duplicate first
                $initials .= isset($string[1]) ? strtoupper($string[1]) : $initials;
            }

            return $initials;
        });

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
                        'display' => __('admin-bar::strings.enabled'),
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
                    'admin_bar_site_label' => [
                        'type' => 'button_group',
                        'display' => __('Short Site Label'),
                        'instructions' => __('What to show as a short site label.'),
                        'width' => '50',
                        'default' => 'locale',
                        'options' => [
                            'locale' => __('Locale'),
                            'name' => __('Name'),
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
