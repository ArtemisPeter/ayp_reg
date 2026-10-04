<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const LOCKOUT_SECONDS = 60;

    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors(['username' => "Too many sign-in attempts. Please try again in {$seconds} seconds."])->onlyInput('username');
        }

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);

            return back()->withErrors(['username' => 'These credentials do not match our records.'])->onlyInput('username');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect('/dashboard');
    }

    /**
     * Failed attempts are counted per username and IP address together, so one person guessing
     * does not lock out everyone else signing in from the same camp Wi-Fi.
     */
    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('username')->toString()).'|'.$request->ip());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
