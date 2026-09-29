<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminLocaleController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $locale = $request->validate(['locale' => ['required', Rule::in(['pl', 'uk'])]])['locale'];
        $request->session()->put('admin_locale', $locale);
        if ($user = $request->user()) {
            $user->forceFill(['admin_locale' => $locale])->save();
        }

        $previous = url()->previous();
        $path = parse_url($previous, PHP_URL_PATH) ?: '';
        $safe = parse_url($previous, PHP_URL_HOST) === $request->getHost()
            && ($path === '/admin' || str_starts_with($path, '/admin/'));

        return redirect($safe ? $previous : '/admin');
    }
}
