<?php

namespace ElSchneider\StatamicAdminBar\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
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
            ...$this->siteItems(),
            ...$this->contentItems(),
            ...$this->userItems(),
            ...$this->entryItems(),
            'csrfToken' => csrf_token(),
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
            ],
        ];
    }

    private function collectionItems()
    {
        $site = Site::current()->handle();

        $collections = Collection::all()->map(function ($collection) use ($site) {

            $blueprints = $collection->entryBlueprints()->select('title', 'handle')->map(fn ($blueprint) => [
                'name' => 'Create ' . $blueprint['title'],
                'url' => cp_route('collections.entries.create', [$collection, $site, 'blueprint' => $blueprint['handle']]),
                'icon' => 'mdi-light:plus-circle',
            ]);

            return [
                'name' => $collection->title,
                'url' => cp_route('collections.show', $collection),
                'icon' => 'mdi-light:folder',
                'items' => [
                    ...$blueprints,
                    [
                        'type' => 'divider',
                    ],
                    [
                        'name' => 'All Entries',
                        'url' => cp_route('collections.show', $collection),
                    ],
                ],
            ];
        })->toArray();

        return [
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

        return [
            'taxonomies' => [
                'name' => __('Taxonomies'),
                'icon' => 'mdi-light:tag',
                'items' => Taxonomy::all()->map(function ($taxonomy) use ($site) {
                    $blueprints = $taxonomy->termBlueprints()->select('title', 'handle')->map(fn ($blueprint) => [
                        'name' => 'Create ' . $blueprint['title'],
                        'icon' => 'mdi-light:plus-circle',
                        'url' => cp_route('taxonomies.terms.create', [$taxonomy, $site, 'blueprint' => $blueprint['handle']]),
                    ]);

                    return [
                        'name' => $taxonomy->title,
                        'icon' => 'mdi-light:tag',
                        'url' => cp_route('taxonomies.show', $taxonomy),
                        'items' => [
                            ...$blueprints,
                            [
                                'type' => 'divider',
                            ],
                            [
                                'name' => 'All Terms',
                                'url' => cp_route('taxonomies.show', $taxonomy),
                            ],
                        ],
                    ];
                })->toArray(),
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
                'items' => [
                    [
                        'name' => __('User Preferences'),
                        'url' => route('statamic.cp.preferences.default.edit'),
                        'icon' => 'mdi-light:settings',
                    ],
                    [
                        'name' => __('Edit User'),
                        'url' => $editUrl,
                        'icon' => 'mdi-light:account',
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
