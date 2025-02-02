<?php

namespace ElSchneider\StatamicAdminBar\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Preference;
use Statamic\Facades\Site as SiteFacade;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Http\Controllers\Controller;
use Statamic\Sites\Site;

class AdminBarController extends Controller
{
    private string $path;

    public function __invoke(Request $request)
    {
        $url = $request->header('Referer') ?? $request->url();
        $this->path = parse_url($url)['path'] ?? '/';

        if (! config('statamic.cp.enabled')) {
            return response()->json([]);
        }

        if (! auth()->check()) {
            return response()->json(['login' => route('statamic.cp.login')], 403);
        }

        if (! auth()->user()->can('access cp') || ! auth()->user()->can('view admin bar')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! Preference::get('admin_bar_enabled', true)) {
            return response()->json(['disabled' => true]);
        }

        app()->setLocale(auth()->user()->preferred_locale);

        return response()->json([
            'environment' => config('app.env'),
            'csrfToken' => csrf_token(),
            ...$this->siteItems(),
            ...$this->contentItems(),
            ...$this->userItems(),
            ...$this->entryItems(),
        ]);
    }

    private function getCurrentSite(): Site
    {
        return Blink::once('adminBarCurrentSite', function () {
            $request = request();
            $url = $request->header('Referer') ?? $request->url();
            $parsedUrl = parse_url($url);
            $requestHost = $parsedUrl['host'] ?? null;
            $requestPath = $parsedUrl['path'] ?? '/';

            return SiteFacade::all()
                ->sortByDesc(fn ($site) => strlen(parse_url($site->url())['path'] ?? '/'))
                ->first(fn ($site) => $this->siteMatchesRequest($site, $requestHost, $requestPath))
                ?? SiteFacade::default();
        });
    }

    private function siteMatchesRequest(Site $site, ?string $requestHost, string $requestPath): bool
    {
        $siteUrl = parse_url($site->url());
        $siteHost = $siteUrl['host'] ?? null;
        $sitePath = $siteUrl['path'] ?? '/';

        return (! $siteHost || $siteHost === $requestHost) &&
            str_starts_with($requestPath, $sitePath);
    }

    private function siteItems()
    {
        $startUrl = route('statamic.cp.' . config('statamic.cp.start_page'));

        return [
            'site' => [
                ...$this->getCurrentSite()->toArray(),
                'homeAction' => [
                    'name' => __('Control Panel'),
                    'url' => $startUrl,
                ],
            ],
        ];
    }

    private function contentItems()
    {
        return [
            'content' => [
                ...$this->collectionItems(),
                ...$this->taxonomyItems(),
                ...$this->globalsItems(),
                ...$this->assetsItems(),
            ],
        ];
    }

    private function hasPermission(string $action, string $handle, string $type): bool
    {
        return auth()->user()->can("{$action} {$handle} {$type}");
    }

    private function collectionItems()
    {
        $site = $this->getCurrentSite()->handle();

        $collections = Collection::all()
            ->filter(fn ($collection) => $this->hasPermission('view', $collection->handle(), 'entries'))
            ->map(function ($collection) use ($site) {
                $handle = $collection->handle();

                $blueprints = $this->hasPermission('create', $handle, 'entries')
                    ? $collection->entryBlueprints()->select('title', 'handle')->map(fn ($blueprint) => [
                        'name' => 'Create ' . $blueprint['title'],
                        'url' => cp_route('collections.entries.create', [$collection, $site, 'blueprint' => $blueprint['handle']]),
                        'icon' => 'mdi:plus-circle',
                    ])
                    : collect([]);

                $items = collect();

                if ($blueprints->isNotEmpty()) {
                    $items = $items->merge($blueprints)->push(['type' => 'divider']);
                }

                $items = $items->push([
                    'name' => 'All Entries',
                    'url' => cp_route('collections.show', $collection),
                ]);

                return [
                    'name' => $collection->title,
                    'url' => cp_route('collections.show', $collection),
                    'icon' => 'mdi:folder',
                    'items' => $items->toArray(),
                ];
            })->values()->toArray();

        return empty($collections) ? [] : [
            'collections' => [
                'name' => __('Collections'),
                'icon' => 'mdi:folder-multiple',
                'items' => $collections,
            ],
        ];
    }

    private function taxonomyItems()
    {
        $site = $this->getCurrentSite()->handle();

        $taxonomies = Taxonomy::all()
            ->filter(fn ($taxonomy) => $this->hasPermission('view', $taxonomy->handle(), 'terms'))
            ->map(function ($taxonomy) use ($site) {
                $handle = $taxonomy->handle();

                $blueprints = $this->hasPermission('create', $handle, 'terms')
                    ? $taxonomy->termBlueprints()->select('title', 'handle')->map(fn ($blueprint) => [
                        'name' => 'Create ' . $blueprint['title'],
                        'icon' => 'mdi:plus-circle',
                        'url' => cp_route('taxonomies.terms.create', [$taxonomy, $site, 'blueprint' => $blueprint['handle']]),
                    ])
                    : collect([]);

                $items = collect();

                if ($blueprints->isNotEmpty()) {
                    $items = $items->merge($blueprints)->push(['type' => 'divider']);
                }

                $items = $items->push([
                    'name' => __('All Terms'),
                    'url' => cp_route('taxonomies.show', $taxonomy),
                ]);

                return [
                    'name' => $taxonomy->title,
                    'icon' => 'mdi:tag',
                    'url' => cp_route('taxonomies.show', $taxonomy),
                    'items' => $items->toArray(),
                ];
            })->values()->toArray();

        return empty($taxonomies) ? [] : [
            'taxonomies' => [
                'name' => __('Taxonomies'),
                'icon' => 'mdi:tags',
                'items' => $taxonomies,
            ],
        ];
    }

    private function globalsItems()
    {
        $globals = GlobalSet::all()
            ->filter(fn ($global) => $this->hasPermission('edit', $global->handle(), 'globals'))
            ->map(function ($global) {
                return [
                    'name' => $global->title(),
                    'url' => cp_route('globals.variables.edit', $global->handle()),
                    'icon' => 'mdi:globe',
                ];
            })->values()->toArray();

        return empty($globals) ? [] : [
            'globals' => [
                'name' => __('Globals'),
                'icon' => 'mdi:globe',
                'items' => $globals,
            ],
        ];
    }

    private function assetsItems()
    {
        $containers = \Statamic\Facades\AssetContainer::all()
            ->filter(fn ($container) => $this->hasPermission('view', $container->handle(), 'assets'));

        if ($containers->isEmpty()) {
            return [];
        }

        $assetsItems =
        [
            'assets' => [
                'name' => __('Assets'),
                'icon' => 'mdi:images',
            ],
        ];

        if ($containers->count() === 1) {
            $container = $containers->first();
            $containerUrl = cp_route('assets.browse.show', $container->handle());

            $assetsItems['assets']['name'] = $container->title();
            $assetsItems['assets']['url'] = $containerUrl;

            return $assetsItems;
        }

        $items = $containers->map(function ($container) {
            return [
                'name' => $container->title(),
                'icon' => 'mdi:folder',
                'url' => cp_route('assets.browse.show', $container->handle()),
            ];
        })->values()->toArray();

        $assetsItems['assets']['items'] = $items;

        return $assetsItems;
    }

    private function entryItems()
    {
        $entry = Entry::query()->where('url', $this->path)->first();
        $term = Term::findByUri($this->path);
        $currentSite = $this->getCurrentSite();

        if (! $entry && ! $term) {
            return ['entry' => null];
        }

        $entity = $entry ?? $term;
        $type = $entry ? 'entries' : 'terms';
        $handle = $entry
            ? $entity->collection()->handle()
            : $entity->taxonomy()->handle();

        // Optional publishing and expiration dates
        $publishDate = $entity->get('publish_date') ?? null;
        $expirationDate = $entity->get('expiration_date') ?? null;

        $localizations = SiteFacade::all()->map(function ($site) use ($entity, $currentSite) {
            $localized = $entity->in($site->handle());
            $origin = $entity->locale() === $site->handle();

            $url = null;
            $editUrl = null;
            $status = null;

            if ($localized) {
                $url = $localized->url();
                $editUrl = $localized->editUrl();
                $status = $localized->status();
            } elseif (! $origin) {
                // TODO: make this more sophisticated
                $editUrl = $entity->editUrl();
            }

            return [
                'site_name' => $site->name(),
                'locale' => $site->locale(),
                'short_locale' => substr($site->locale(), 0, 2),
                'title' => $site->name(),
                'url' => $url,
                'edit_url' => $editUrl,
                'origin' => $origin,
                'is_current' => $site->handle() === $currentSite->handle(),
                'status' => $status,
            ];
        })->values();

        $item = [
            'entry' => [
                'id' => $entity->id(),
                'title' => $entity->get('title'),
                'status' => __($entity->status()),
                'published' => $entity->published(),
                'locale' => $entity->locale(),
                'short_locale' => $entity->site()->lang(),
                'localizations' => $localizations,
                'publish_date' => $publishDate,
                'expiration_date' => $expirationDate,
            ],
        ];

        if (! $this->hasPermission('edit', $handle, $type)) {
            return $item;
        }

        $item['entry']['editAction'] = [
            'name' => __('Edit'),
            'url' => $entity->editUrl(),
        ];

        $item['entry']['publishAction'] = [
            'name' => __('Publish'),
            'url' => route('statamic.admin-bar.entry.update', $entity->id()),
            'method' => 'PUT',
        ];

        return $item;
    }

    private function getPreferences()
    {
        $statamic_theme = Preference::get('theme', 'dark');
        $dark_mode = Preference::get('admin_bar_dark_mode', $statamic_theme);

        return [
            'darkMode' => $dark_mode === 'dark',
        ];
    }

    private function userItems()
    {
        $user = auth()->user()->toArray();
        $editUrl = $user['edit_url'];

        unset($user['edit_url']);

        $preferences = $this->getPreferences();

        return [
            'user' => [
                ...$user,
                'icon' => 'mdi:account',
                'preferences' => $preferences,
                'items' => [
                    [
                        'name' => __('Preferences'),
                        'url' => route('statamic.cp.preferences.user.edit'),
                        'icon' => 'mdi:settings',
                    ],
                    [
                        'name' => __('Edit User'),
                        'url' => $editUrl,
                        'icon' => 'mdi:account-edit',
                    ],
                    [
                        'name' => __('Logout'),
                        'url' => route('statamic.cp.logout'),
                        'icon' => 'mdi:logout',
                        'class' => 'text-destructive',
                    ],
                ],
            ],
        ];
    }
}
