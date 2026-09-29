<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->admin_locale ?? $request->session()->get('admin_locale', 'pl');
        app()->setLocale(in_array($locale, ['pl', 'uk'], true) ? $locale : 'pl');

        return $next($request);
    }
}
