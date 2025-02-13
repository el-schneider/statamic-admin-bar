<?php

namespace ElSchneider\StatamicAdminBar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetAdminBarLocale
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            app()->setLocale(auth()->user()->preferred_locale);
        }

        return $next($request);
    }
}
