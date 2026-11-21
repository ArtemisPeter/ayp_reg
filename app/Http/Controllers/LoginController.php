<?php

namespace App\Http\Controllers;

use App\Auth\SignInThrottle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request, SignInThrottle $throttle): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($seconds = $throttle->secondsUntilAllowed($request)) {
            return back()->withErrors(['username' => "Too many sign-in attempts. Please try again in {$seconds} seconds."])->onlyInput('username');
        }

        if (! Auth::attempt($credentials)) {
            $throttle->recordFailure($request);

            return back()->withErrors(['username' => 'These credentials do not match our records.'])->onlyInput('username');
        }

        $throttle->recordSuccess($request);
        $request->session()->regenerate();

        return redirect('/dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
