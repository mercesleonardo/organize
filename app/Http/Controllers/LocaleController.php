<?php

namespace App\Http\Controllers;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    /**
     * Define o idioma da sessão e, se autenticado, persiste em {@see User::$locale}.
     */
    public function update(Request $request): RedirectResponse
    {
        $supported = array_keys(config('locales.supported', []));

        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in($supported)],
        ]);

        $locale = $validated['locale'];

        $request->session()->put('locale', $locale);
        App::setLocale($locale);

        if ($request->user() !== null) {
            $request->user()->forceFill(['locale' => $locale])->save();
        }

        return redirect()->back(fallback: route('home'));
    }
}
