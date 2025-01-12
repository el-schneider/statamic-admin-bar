<?php

namespace ElSchneider\StatamicAdminBar\Http\Controllers;

use Statamic\Http\Controllers\Controller;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Entry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBarController extends Controller
{
    public function index(Request $request)
    {
        // Get current URI and find matching entry
        $uri = $request->input('uri');
        $entry = Entry::findByUri($uri);

        $nav = Nav::build();

        // Get content items from the navigation
        $contentSection = $nav->filter(function ($item) {
            return $item['display'] === 'Content';
        })->first();

        // Transform the content items into a simplified structure
        $navItems = $contentSection['items']->map(function ($item) {
            $urlPath = parse_url($item->url(), PHP_URL_PATH);
            $lastSegment = basename($urlPath);

            return [
                'name' => Str::title($lastSegment),
                'url' => $item->url(),
                'icon' => $item->icon(),
                'section' => $item->section(),
            ];
        });

        return response()->json([
            'navItems' => $navItems,
            'currentEntry' => $entry ? [
                'id' => $entry->id(),
                'title' => $entry->get('title'),
                'uri' => $entry->uri(),
                'edit_url' => $entry->editUrl(),
            ] : null,
        ]);
    }
}
