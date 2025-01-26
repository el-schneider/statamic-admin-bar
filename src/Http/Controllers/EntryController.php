<?php

namespace ElSchneider\StatamicAdminBar\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\Entries\Entry as EntryModel;
use Statamic\Facades\Entry;

class EntryController extends Controller
{
    public function show(string $id): JsonResponse
    {
        $entry = Entry::find($id);

        if (! $entry) {
            return response()->json(['error' => 'Entry not found'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $entry->id(),
                'title' => $entry->get('title'),
                'slug' => $entry->slug(),
                'collection' => $entry->collection()->handle(),
                'data' => $entry->data()->all(),
            ],
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        /** @var EntryModel|null */
        $entry = Entry::find($id);

        if (! $entry) {
            return response()->json(['error' => 'Entry not found'], 404);
        }

        $entry->published(! $entry->published());
        $entry->save();

        return response()->json([
            'message' => 'Entry publish state updated successfully',
            'data' => [
                'id' => $entry->id(),
                'published' => $entry->published(),
            ],
        ]);
    }
}
