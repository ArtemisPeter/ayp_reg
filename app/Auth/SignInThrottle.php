<?php

namespace App\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Limits failed sign-ins the same way for the website and the gate app.
 */
class SignInThrottle
{
    private const MAX_ATTEMPTS = 5;

    private const LOCKOUT_SECONDS = 60;

    private const MAX_ATTEMPTS_PER_ADDRESS = 30;

    private const ADDRESS_LOCKOUT_SECONDS = 900;

    /**
     * The seconds left before another attempt is allowed, or null when signing in is allowed now.
     */
    public function secondsUntilAllowed(Request $request): ?int
    {
        $throttleKey = $this->throttleKey($request);
        $addressKey = $this->addressThrottleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS) || RateLimiter::tooManyAttempts($addressKey, self::MAX_ATTEMPTS_PER_ADDRESS)) {
            return max(RateLimiter::availableIn($throttleKey), RateLimiter::availableIn($addressKey));
        }

        return null;
    }

    public function recordFailure(Request $request): void
    {
        RateLimiter::hit($this->throttleKey($request), self::LOCKOUT_SECONDS);
        RateLimiter::hit($this->addressThrottleKey($request), self::ADDRESS_LOCKOUT_SECONDS);
    }

    /**
     * Clears the username's failures. The address count is never cleared by a success.
     */
    public function recordSuccess(Request $request): void
    {
        RateLimiter::clear($this->throttleKey($request));
    }

    /**
     * Failed attempts are counted per username and IP address together, so one person guessing
     * does not lock out everyone else signing in from the same camp Wi-Fi.
     */
    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('username')->toString()).'|'.$request->ip());
    }

    /**
     * Failed attempts from one IP address across every username, so nobody can try one common
     * password against each account in turn. The limit is generous because the whole camp may
     * share one Wi-Fi address, and a successful sign-in never clears it.
     */
    private function addressThrottleKey(Request $request): string
    {
        return 'login-address|'.$request->ip();
    }
}
