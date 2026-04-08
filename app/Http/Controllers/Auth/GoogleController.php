<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleController extends Controller
{
    public function redirect(): SymfonyRedirectResponse
    {
        return $this->googleProvider()->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = $this->googleProvider()->user();
        } catch (InvalidStateException) {
            return redirect()
                ->route('login')
                ->with('status', __('Google sign-in session expired. Please try again, using the same site address you started from (e.g. only localhost or only 127.0.0.1).'));
        }

        $email    = $googleUser->getEmail();
        $googleId = $googleUser->getId();
        $name     = $googleUser->getName() ?: $googleUser->getNickname() ?: __('User');

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $user = User::query()->create([
                'name'      => $name,
                'email'     => $email,
                'google_id' => $googleId,
                'password'  => Str::password(64),
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();
        } else {
            $user->forceFill([
                'google_id'         => $user->google_id ?: $googleId,
                'email_verified_at' => $user->email_verified_at ?: now(),
            ])->save();
        }

        Auth::login($user, remember: true);

        return redirect()->route('dashboard');
    }

    private function googleProvider(): SocialiteProvider
    {
        $driver = Socialite::driver('google')->scopes(['openid', 'profile', 'email']);

        if (config('services.google.stateless')) {
            $driver = $driver->stateless();
        }

        return $driver;
    }
}
