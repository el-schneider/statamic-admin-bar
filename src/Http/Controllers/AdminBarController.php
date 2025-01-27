<?php

namespace ElSchneider\StatamicAdminBar\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Http\Controllers\Controller;

class AdminBarController extends Controller
{
    private string $uri;

    public function __invoke(Request $request)
    {
        $this->uri = $request->validate(['uri' => 'string'])['uri'];

        if (! config('statamic.cp.enabled')) {
            return response()->json([]);
        }

        if (! auth()->check() || ! auth()->user()->can('access cp')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        app()->setLocale(auth()->user()->preferred_locale);

        return response()->json([
            'environment' => config('app.env'),
            'csrfToken' => csrf_token(),
            'sites' => Site::all()->map(fn($site) => [
                'handle' => $site->handle(),
                'name' => $site->name(),
                'lang' => $site->lang(),
                'locale' => $site->locale(),
                'url' => $site->url(),
            ])->values()->toArray(),
            ...$this->siteItems(),
            ...$this->contentItems(),
            ...$this->userItems(),
            ...$this->entryItems(),
        ]);
    }

    private function siteItems()
    {
        $startUrl = route('statamic.cp.' . config('statamic.cp.start_page'));

        return [
            'site' => [
                ...Site::current()->toArray(),
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
        $site = Site::current()->handle();

        $collections = Collection::all()
            ->filter(fn ($collection) => $this->hasPermission('view', $collection->handle(), 'entries'))
            ->map(function ($collection) use ($site) {
                $handle = $collection->handle();

                $blueprints = $this->hasPermission('create', $handle, 'entries')
                    ? $collection->entryBlueprints()->select('title', 'handle')->map(fn ($blueprint) => [
                        'name' => 'Create ' . $blueprint['title'],
                        'url' => cp_route('collections.entries.create', [$collection, $site, 'blueprint' => $blueprint['handle']]),
                        'icon' => 'mdi-light:plus-circle',
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
                    'icon' => 'mdi-light:folder',
                    'items' => $items->toArray(),
                ];
            })->values()->toArray();

        return empty($collections) ? [] : [
            'collections' => [
                'name' => __('Collections'),
                'icon' => 'mdi-light:folder-multiple',
                'items' => $collections,
            ],
        ];
    }

    private function taxonomyItems()
    {
        $site = Site::current()->handle();

        $taxonomies = Taxonomy::all()
            ->filter(fn ($taxonomy) => $this->hasPermission('view', $taxonomy->handle(), 'terms'))
            ->map(function ($taxonomy) use ($site) {
                $handle = $taxonomy->handle();

                $blueprints = $this->hasPermission('create', $handle, 'terms')
                    ? $taxonomy->termBlueprints()->select('title', 'handle')->map(fn ($blueprint) => [
                        'name' => 'Create ' . $blueprint['title'],
                        'icon' => 'mdi-light:plus-circle',
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
                    'icon' => 'mdi-light:tag',
                    'url' => cp_route('taxonomies.show', $taxonomy),
                    'items' => $items->toArray(),
                ];
            })->values()->toArray();

        return empty($taxonomies) ? [] : [
            'taxonomies' => [
                'name' => __('Taxonomies'),
                'icon' => 'mdi-light:tag',
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
                    'icon' => 'mdi-light:circle',
                ];
            })->values()->toArray();

        return empty($globals) ? [] : [
            'globals' => [
                'name' => __('Globals'),
                'icon' => 'mdi-light:circle',
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
                'icon' => 'mdi-light:image',
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
                'icon' => 'mdi-light:folder',
                'url' => cp_route('assets.browse.show', $container->handle()),
            ];
        })->values()->toArray();

        $assetsItems['assets']['items'] = $items;

        return $assetsItems;
    }

    private function entryItems()
    {
        if (! $this->uri) {
            return [];
        }

        // Entry::findByUri() may return a \Statamic\Structures\Pages Instance, which is why this uses a query builder
        $entry = Entry::query()->where('uri', $this->uri)->first();
        $term = Term::findByUri($this->uri);

        if (! $entry && ! $term) {
            return ['entry' => null];
        }

        $entity = $entry ?? $term;
        $type = $entry ? 'entries' : 'terms';
        $handle = $entry
            ? $entity->collection()->handle()
            : $entity->taxonomy()->handle();

        $item = [
            'entry' => [
                'id' => $entity->id(),
                'title' => $entity->get('title'),
                'published' => $entity->published(),
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

    private function userItems()
    {
        $user = auth()->user()->toArray();
        $editUrl = $user['edit_url'];

        unset($user['edit_url']);

        return [
            'user' => [
                ...$user,
                'icon' => 'mdi-light:account',
                'items' => [
                    [
                        'name' => __('Preferences'),
                        'url' => route('statamic.cp.preferences.user.edit'),
                        'icon' => 'mdi-light:settings',
                    ],
                    [
                        'name' => __('Edit User'),
                        'url' => $editUrl,
                        'icon' => 'mdi-light:pencil',
                    ],
                    [
                        'name' => __('Logout'),
                        'url' => route('statamic.cp.logout'),
                        'icon' => 'mdi-light:logout',
                        'class' => 'text-red-500',
                    ],
                ],
            ],
        ];
    }
}
