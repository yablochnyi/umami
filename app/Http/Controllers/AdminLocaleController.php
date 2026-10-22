<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAdminLocaleRequest;
use Illuminate\Http\RedirectResponse;

class AdminLocaleController extends Controller
{
    public function __invoke(UpdateAdminLocaleRequest $request): RedirectResponse
    {
        $locale = $request->validated('locale');
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
