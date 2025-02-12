<?php

namespace ElSchneider\StatamicAdminBar\Http\Controllers;

use Statamic\Facades\Preference;
use Statamic\Http\Controllers\Controller;

class AccessController extends Controller
{
    public function canViewAdminBar(): bool
    {
        if (! config('statamic.cp.enabled')) {
            return false;
        }

        if (! auth()->check()) {
            return false;
        }

        if (! auth()->user()->can('access cp') || ! auth()->user()->can('view admin bar')) {
            return false;
        }

        return Preference::get('admin_bar_enabled', true);
    }

    public function hasAdminBarAccess(): bool
    {
        return request()->cookie('has_admin_bar_access') !== null;
    }

    public function getLoginUrl(): string
    {
        return route('statamic.cp.login');
    }
}
