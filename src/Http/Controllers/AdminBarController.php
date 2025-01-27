<?php

namespace ElSchneider\StatamicAdminBar\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
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

        return response()->json([
            'environment' => config('app.env'),
            'csrfToken' => csrf_token(),
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
                    'name' => 'Control Panel',
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
                'assets' => [
                    'name' => __('Assets'),
                    'icon' => 'mdi-light:image',
                    'url' => cp_route('assets.browse.index'),
                ],
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
                    'name' => 'All Terms',
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

    private function entryItems()
    {
        if (! $this->uri) {
            return [];
        }

        // TODO: remove fallback as this is for testing
        $entry = Entry::findByUri($this->uri) ?? Entry::all()->first();

        return [
            'entry' => $entry ? [
                'id' => $entry->id(),
                'title' => $entry->get('title'),
                'published' => $entry->published(),
                'editAction' => [
                    'name' => 'Edit',
                    'url' => $entry->editUrl(),
                ],
                'publishAction' => [
                    'name' => 'Publish',
                    'url' => route('statamic.admin-bar.entry.update', $entry->id()),
                    'method' => 'PUT',
                ],
            ] : null,
        ];
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
                        'name' => __('User Preferences'),
                        'url' => route('statamic.cp.preferences.default.edit'),
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
