<?php

namespace ElSchneider\StatamicAdminBar\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
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
            ...$this->siteActions(),
            ...$this->collectionActions(),
            ...$this->userActions(),
            ...$this->entryActions(),
        ]);
    }

    private function siteActions()
    {
        $startUrl = route('statamic.cp.' . config('statamic.cp.start_page'));

        return [
            'site' => [
                ...Site::current()->toArray(),
                'actions' => [
                    [
                        'name' => '⚡ Control Panel',
                        'url' => $startUrl,
                    ],
                ],
            ],
        ];
    }

    private function collectionActions()
    {
        $site = Site::current()->handle();

        $collections = Collection::all()->map(function ($collection) use ($site) {

            $blueprints = $collection->entryBlueprints()->select('title', 'handle')->map(fn ($blueprint) => [
                'name' => 'Create ' . $blueprint['title'],
                'url' => cp_route('collections.entries.create', [$collection, $site, 'blueprint' => $blueprint['handle']]),
            ]);

            return [
                'name' => $collection->title,
                'url' => cp_route('collections.show', $collection),
                'actions' => [
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
            'collections' => $collections,
        ];
    }

    private function entryActions()
    {
        if (! $this->uri) {
            return [];
        }

        // TODO: remove fallback as this is for testing
        $entry = Entry::findByUri($this->uri) ?? Entry::all()->first();

        return [
            'currentEntry' => $entry ? [
                'id' => $entry->id(),
                'title' => $entry->get('title'),
                'actions' => [
                    [
                        'name' => 'Edit',
                        'url' => $entry->editUrl(),
                    ],
                ],
            ] : null,
        ];
    }

    private function userActions()
    {
        $user = auth()->user()->toArray();
        $editUrl = $user['edit_url'];

        unset($user['edit_url']);

        return [
            'user' => [
                ...$user,
                'actions' => [
                    [
                        'name' => 'Preferences',
                        'url' => route('statamic.cp.preferences.default.edit'),
                    ],
                    [
                        'name' => 'Edit User',
                        'url' => $editUrl,
                        'icon' => 'user',
                    ],
                    [
                        'name' => 'Logout',
                        'url' => route('statamic.cp.logout'),
                        'class' => 'text-red-500',
                    ],
                ],
            ],
        ];
    }
}
