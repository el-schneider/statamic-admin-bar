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
use Statamic\Support\Str;

class AdminBarController extends Controller
{
    private string $path;

    private AccessController $authController;

    public function __construct(AccessController $authController)
    {
        $this->authController = $authController;
    }

    public function __invoke(Request $request)
    {
        $url = $request->header('Referer') ?? $request->url();
        $this->path = parse_url($url)['path'] ?? '/';

        if (! $this->authController->canViewAdminBar()) {
            return response()->json(['login' => $this->authController->getLoginUrl()], 403);
        }

        return response()->json([
            'environment' => config('app.env'),
            'csrf_token' => csrf_token(),
            ...$this->siteItems(),
            ...$this->contentItems(),
            ...$this->userItems(),
            ...$this->entryItems(),
            ...$this->cacheItems(),
        ]);
    }

    private function getAllSites()
    {
        return Blink::once('adminBarAllSites', function () {
            return SiteFacade::authorized();
        });
    }

    private function getCurrentSite(): Site
    {
        return Blink::once('adminBarCurrentSite', function () {
            $request = request();
            $url = $request->header('Referer') ?? $request->url();
            $parsedUrl = parse_url($url);
            $requestHost = $parsedUrl['host'] ?? null;
            $requestPath = $parsedUrl['path'] ?? '/';

            return $this->getAllSites()
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
        $startUrl = config('statamic.cp.route') . Str::ensureLeft(Preference::get('start_page', config('statamic.cp.start_page')), '/');

        return [
            'site' => [
                ...$this->getCurrentSite()->toArray(),
                'home_action' => [
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
                        'name' => __('admin-bar::strings.create_entity', ['entity' => $blueprint['title']]),
                        'url' => cp_route('collections.entries.create', [$collection, $site, 'blueprint' => $blueprint['handle']]),
                        'icon' => 'mdi:plus-circle',
                    ])
                    : collect([]);

                $items = collect();

                if ($blueprints->isNotEmpty()) {
                    $items = $items->merge($blueprints)->push(['type' => 'divider']);
                }

                $items = $items->push([
                    'name' => __('admin-bar::strings.all_entries'),
                    'url' => cp_route('collections.show', $collection),
                ]);

                return [
                    'name' => $collection->title,
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
                        'name' => __('admin-bar::strings.create_entity', ['entity' => $blueprint['title']]),
                        'icon' => 'mdi:plus-circle',
                        'url' => cp_route('taxonomies.terms.create', [$taxonomy, $site, 'blueprint' => $blueprint['handle']]),
                    ])
                    : collect([]);

                $items = collect();

                if ($blueprints->isNotEmpty()) {
                    $items = $items->merge($blueprints)->push(['type' => 'divider']);
                }

                $items = $items->push([
                    'name' => __('admin-bar::strings.all_terms'),
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
        $currentSite = $this->getCurrentSite();
        $entry = Entry::query()->where('url', $this->path)->first();
        $term = Term::query()->where('url', $this->path)->first();

        if (! $entry && ! $term) {
            return ['entry' => null];
        }

        $entity = $entry ?? $term;
        $type = $entry ? 'entry' : 'term';
        $typePlural = $entry ? 'entries' : 'terms';
        $handle = $entry ? $entity->collection()->handle() : $entity->taxonomy()->handle();
        $collection = $entry ? $entity->collection() : null;
        $taxonomy = ! $entry ? $entity->taxonomy() : null;
        $status = $entity->status();

        $item = [
            'entry' => [
                'switch_site_label' => __('admin-bar::strings.switch_site'),
                'short_site_label' => Preference::get('admin_bar_site_label', 'locale') === 'name' ? Str::initials($this->getCurrentSite()->name()) : null,
                'localized_status' => $status === 'missing' ? __('admin-bar::strings.missing') : __(ucfirst($status)),
                'short_locale' => $this->getCurrentSite()->shortLocale(),
                'type' => $type,
            ],
        ];

        $sites = $this->getAllSites()
            ->when(isset($collection), function ($sites) use ($collection) {
                return $sites->filter(fn ($site) => $collection->sites()->contains($site->handle()));
            })
            ->when(isset($taxonomy), function ($sites) use ($taxonomy) {
                return $sites->filter(fn ($site) => $taxonomy->sites()->contains($site->handle()));
            });

        if ($sites->isEmpty()) {
            return $item;
        }

        $item['entry']['localizations'] = $sites->map(function ($site) use ($entity, $currentSite, $handle, $typePlural) {
            $localized = $entity->in($site->handle());
            $origin = $entity?->origin() ?? $entity;
            // locale helps differentiate between different terms, as their id's look like this: 'tags::delightful'
            $is_origin = $localized?->locale . $localized?->id === $origin?->locale . $origin?->id;

            $siteData = [
                'title' => $site->name,
                'locale' => $site->locale,
                'site_name' => $site->name,
                'short_locale' => $site->shortLocale,
                'is_current' => $site->handle === $currentSite->handle,
                'is_origin' => $is_origin,
            ];

            if ($localized) {
                $localizedData = [
                    'url' => $localized->url,
                    'status' => $localized->status,
                    'published' => $localized->published,
                    'publish_date' => $localized->publish_date,
                    'expiration_date' => $localized->expiration_date,
                    'localized_status' => $localized->status ? __(ucfirst($localized->status)) : null,
                ];

                if ($this->hasPermission('edit', $handle, $typePlural)) {
                    $localizedData['edit_action'] = [
                        'name' => __('Edit'),
                        'url' => $localized->editUrl(),
                    ];

                    $localizedData['update_action'] = [
                        'name' => __('Update'),
                        'url' => route('statamic.admin-bar.entry.update', $localized->id()),
                        'method' => 'PUT',
                    ];
                }

                return [
                    ...$siteData,
                    ...$localizedData,
                ];
            } elseif (! $is_origin) {
                $siteData['status'] = 'missing';
                $siteData['localized_status'] = __('admin-bar::strings.missing');

                // TODO: make this more sophisticated
                if ($this->hasPermission('edit', $handle, $typePlural)) {
                    $editAction = [
                        'name' => __('Edit'),
                        'url' => $entity->editUrl(),
                    ];

                    $siteData['edit_action'] = $editAction;
                }
            }

            return $siteData;
        })->values();

        return $item;
    }

    private function getPreferences()
    {
        $statamic_theme = Preference::get('theme', 'dark');
        $dark_mode = Preference::get('admin_bar_dark_mode', $statamic_theme);

        return [
            'dark_mode' => $dark_mode === 'dark',
        ];
    }

    private function userItems()
    {
        $user = auth()->user();

        $preferences = $this->getPreferences();

        return [
            'user' => [
                'initials' => Str::initials($user->name),
                'email' => $user->email,
                'avatar' => $user->avatar,
                'roles' => $user->roles()->pluck('title'),
                'groups' => $user->groups()->pluck('title'),
                'roles_label' => __('Roles'),
                'groups_label' => __('Groups'),
                'is_super' => $user->super,
                'preferences' => $preferences,
                'items' => [
                    [
                        'name' => __('Preferences'),
                        'url' => route('statamic.cp.preferences.user.edit'),
                        'icon' => 'mdi:settings',
                    ],
                    [
                        'name' => __('Edit User'),
                        'url' => $user->editUrl(),
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

    private function cacheItems()
    {
        if (! auth()->user()->can('manage cache')) {
            return [];
        }

        $items = [];

        if (config('statamic.static_caching.strategy') !== null) {
            $items[] = [
                'name' => __('Static Page Cache'),
                'icon' => 'mdi:file-document',
                'url' => route('statamic.admin-bar.cache.clear', 'static'),
                'method' => 'POST',
            ];
        }

        $items = array_merge($items, [
            [
                'name' => __('Content Stache'),
                'icon' => 'mdi:database',
                'url' => route('statamic.admin-bar.cache.clear', 'stache'),
                'method' => 'POST',
            ],
            [
                'name' => __('Application Cache'),
                'icon' => 'mdi:application',
                'url' => route('statamic.admin-bar.cache.clear', 'application'),
                'method' => 'POST',
            ],
            [
                'name' => __('Image Cache'),
                'icon' => 'mdi:image',
                'url' => route('statamic.admin-bar.cache.clear', 'image'),
                'method' => 'POST',
            ],
            [
                'name' => __('Clear All'),
                'url' => route('statamic.admin-bar.cache.clear', 'all'),
                'method' => 'POST',
            ],
        ]);

        return [
            'cache' => [
                'name' => __('Cache'),
                'icon' => 'mdi:trash-can-empty',
                'urls' => [
                    'stats' => route('statamic.admin-bar.cache.stats'),
                ],
                'items' => $items,
            ],
        ];
    }
}
